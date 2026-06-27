<?php

if (!defined('ABSPATH')) {
    exit;
}

final class SI_Proposal
{
    public static function build(array $input, array $result, array $settings)
    {
        $price = max(0, (float) ($result['estimated_system_price'] ?? 0));
        $annual_savings = max(0, (float) ($result['estimated_annual_savings'] ?? 0));
        $monthly_consumption = max(0, (float) ($result['monthly_consumption_kwh'] ?? 0));
        $monthly_generation = max(0, (float) ($result['monthly_generation_kwh'] ?? 0));
        $cash_discount = self::percent($settings['cash_discount_percent'] ?? 5);
        $pix_fee = self::percent($settings['mercado_pago_pix_fee'] ?? 0.99);
        $card_processing = self::percent($settings['mercado_pago_card_processing_fee'] ?? 3.99);
        $installment_fees = self::installment_fees($settings['mercado_pago_installment_fees'] ?? '');
        $cash_price = $price * (1 - $cash_discount);
        $payments = array();

        foreach ($installment_fees as $installments => $installment_fee) {
            $total = self::gross_for_net($price, $card_processing + self::percent($installment_fee));
            $payments[] = array(
                'installments' => $installments,
                'total' => round($total, 2),
                'installment' => round($total / $installments, 2),
                'fee_percent' => round(($card_processing + self::percent($installment_fee)) * 100, 2),
            );
        }

        $generation_factors = array(1.15, 1.12, 1.04, 0.96, 0.88, 0.82, 0.84, 0.91, 0.98, 1.04, 1.10, 1.16);
        $factor_sum = array_sum($generation_factors);
        $months = array('Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez');
        $monthly = array();
        foreach ($months as $index => $month) {
            $monthly[] = array(
                'month' => $month,
                'consumption' => round($monthly_consumption, 0),
                'generation' => round(($monthly_generation * 12) * ($generation_factors[$index] / $factor_sum), 0),
            );
        }

        $cash_flow = array();
        $cumulative = -$price;
        $discount_rate = self::percent($settings['financial_discount_rate'] ?? 8);
        $tariff_inflation = self::percent($settings['tariff_inflation_percent'] ?? 5);
        $degradation = self::percent($settings['annual_degradation_percent'] ?? 0.8);
        $maintenance = self::percent($settings['annual_maintenance_percent'] ?? 0.5);
        $npv = -$price;
        $flows = array(-$price);

        for ($year = 1; $year <= 25; $year++) {
            $saving = $annual_savings * pow(1 + $tariff_inflation, $year - 1) * pow(1 - $degradation, $year - 1);
            $net = $saving - ($price * $maintenance);
            $cumulative += $net;
            $npv += $net / pow(1 + $discount_rate, $year);
            $flows[] = $net;
            $cash_flow[] = array(
                'year' => $year,
                'annual' => round($net, 2),
                'cumulative' => round($cumulative, 2),
            );
        }

        return array(
            'proposal_number' => 'SI-' . gmdate('Ymd-His'),
            'issued_at' => current_time('Y-m-d'),
            'valid_days' => max(1, absint($settings['proposal_valid_days'] ?? 10)),
            'investment' => round($price, 2),
            'cash_price' => round($cash_price, 2),
            'pix_price' => round(self::gross_for_net($cash_price, $pix_fee), 2),
            'payments' => $payments,
            'monthly' => $monthly,
            'cash_flow' => $cash_flow,
            'npv' => round($npv, 2),
            'irr_percent' => round(self::irr($flows) * 100, 2),
            'payback_years' => $annual_savings > 0 ? round($price / $annual_savings, 2) : null,
            'area_m2' => round(((int) ($result['module_count'] ?? 0)) * (float) ($settings['module_area_m2'] ?? 2.58), 1),
            'equipment' => self::select_equipment(
                (float) ($result['system_kwp'] ?? 0),
                (int) ($result['module_count'] ?? 0),
                $settings
            ),
            'assumptions' => array(
                'discount_rate_percent' => round($discount_rate * 100, 2),
                'tariff_inflation_percent' => round($tariff_inflation * 100, 2),
                'degradation_percent' => round($degradation * 100, 2),
                'maintenance_percent' => round($maintenance * 100, 2),
            ),
        );
    }

    public static function default_installment_fees()
    {
        return '1:0,2:4.59,3:5.97,4:7.22,5:8.66,6:9.96,7:11.24,8:12.50,9:13.72,10:14.93,11:16.12,12:17.28';
    }

    private static function installment_fees($raw)
    {
        $fees = array();
        foreach (explode(',', (string) $raw) as $item) {
            $parts = array_map('trim', explode(':', $item, 2));
            if (count($parts) !== 2) {
                continue;
            }
            $installments = absint($parts[0]);
            $fee = (float) str_replace(',', '.', $parts[1]);
            if ($installments >= 1 && $installments <= 18 && $fee >= 0 && $fee < 80) {
                $fees[$installments] = $fee;
            }
        }
        if (!$fees) {
            return self::installment_fees(self::default_installment_fees());
        }
        ksort($fees);
        return $fees;
    }

    private static function select_equipment($kwp, $module_count, array $settings)
    {
        $selected = array(
            'supplier' => __('A definir após cotação', 'solar-integradores'),
            'module' => sanitize_text_field($settings['default_module_model'] ?? 'Módulo N-Type 550 W ou superior'),
            'inverter' => sanitize_text_field($settings['default_inverter_model'] ?? 'Inversor compatível com o projeto'),
            'module_count' => $module_count,
            'quoted_cost' => null,
        );
        $best_cost = PHP_FLOAT_MAX;
        $lines = preg_split('/\r\n|\r|\n/', (string) ($settings['supplier_catalog'] ?? ''));
        foreach ($lines as $line) {
            $columns = array_map('trim', str_getcsv($line, '|'));
            if (count($columns) < 6) {
                continue;
            }
            $min = (float) str_replace(',', '.', $columns[1]);
            $max = (float) str_replace(',', '.', $columns[2]);
            $cost = (float) str_replace(array('.', ','), array('', '.'), $columns[5]);
            if ($kwp < $min || $kwp > $max || $cost <= 0 || $cost >= $best_cost) {
                continue;
            }
            $best_cost = $cost;
            $selected = array(
                'supplier' => sanitize_text_field($columns[0]),
                'module' => sanitize_text_field($columns[3]),
                'inverter' => sanitize_text_field($columns[4]),
                'module_count' => $module_count,
                'quoted_cost' => round($cost, 2),
            );
        }
        return $selected;
    }

    private static function gross_for_net($net, $fee)
    {
        return $fee >= 0.95 ? $net : $net / (1 - $fee);
    }

    private static function percent($value)
    {
        return min(0.95, max(0, (float) str_replace(',', '.', (string) $value) / 100));
    }

    private static function irr(array $flows)
    {
        $low = -0.99;
        $high = 10;
        for ($iteration = 0; $iteration < 120; $iteration++) {
            $rate = ($low + $high) / 2;
            $npv = 0;
            foreach ($flows as $period => $flow) {
                $npv += $flow / pow(1 + $rate, $period);
            }
            if ($npv > 0) {
                $low = $rate;
            } else {
                $high = $rate;
            }
        }
        return max(0, ($low + $high) / 2);
    }
}
