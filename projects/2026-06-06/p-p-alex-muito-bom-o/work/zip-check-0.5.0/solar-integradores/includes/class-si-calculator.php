<?php

if (!defined('ABSPATH')) {
    exit;
}

final class SI_Calculator
{
    public static function calculate(array $input)
    {
        $defaults = array(
            'calculation_mode' => 'kwh',
            'customer_type' => 'residential',
            'monthly_consumption' => 0,
            'monthly_bill' => 0,
            'energy_tariff' => 0,
            'connection_type' => 'three_phase',
            'hsp' => 4.8,
            'losses_percent' => 17,
            'off_peak_consumption' => 0,
            'peak_consumption' => 0,
            'off_peak_te' => 0,
            'peak_te' => 0,
            'contracted_demand' => 0,
            'module_power_w' => 550,
            'performance_degradation' => 0.8,
            'price_bands' => self::default_price_bands(),
            'price_range_percent' => 12,
            'public_lighting_fee' => 0,
        );

        $data = wp_parse_args($input, $defaults);
        $tariff = self::positive($data['energy_tariff']);
        $consumption = self::positive($data['monthly_consumption']);

        if ($data['calculation_mode'] === 'bill') {
            if ($tariff <= 0) {
                return new WP_Error('missing_tariff', __('Informe a tarifa de energia para converter a conta em consumo.', 'solar-integradores'));
            }
            $bill = self::positive($data['monthly_bill']);
            $lighting = self::positive($data['public_lighting_fee']);
            $consumption = max(0, ($bill - $lighting) / $tariff);
        }

        $hsp = self::positive($data['hsp']);
        $losses = min(0.45, max(0, self::number($data['losses_percent']) / 100));
        if ($hsp <= 0 || $losses >= 1) {
            return new WP_Error('invalid_solar_data', __('HSP e perdas precisam ser valores válidos.', 'solar-integradores'));
        }

        $availability = self::availability_cost($data['connection_type']);
        $off_peak = self::positive($data['off_peak_consumption']);
        $peak = self::positive($data['peak_consumption']);
        $adjustment_factor = null;
        $equivalent_peak = 0;

        if ($data['customer_type'] === 'commercial_demand') {
            $off_peak_te = self::positive($data['off_peak_te']);
            $peak_te = self::positive($data['peak_te']);
            if ($peak > 0 && ($off_peak_te <= 0 || $peak_te <= 0)) {
                return new WP_Error('missing_time_tariffs', __('Informe as tarifas TE de ponta e fora de ponta.', 'solar-integradores'));
            }
            if ($peak > 0) {
                $adjustment_factor = $off_peak_te / $peak_te;
                $equivalent_peak = $peak / $adjustment_factor;
            }
            $consumption = $off_peak + $equivalent_peak;
            $availability = 0;
        }

        $compensable_consumption = max(0, $consumption - $availability);
        $daily_energy = $compensable_consumption / 30;
        $kwp = $daily_energy / ($hsp * (1 - $losses));
        $monthly_generation = $kwp * $hsp * 30 * (1 - $losses);
        $module_power = max(1, self::positive($data['module_power_w']));
        $module_count = (int) ceil(($kwp * 1000) / $module_power);

        $annual_savings = 0;
        if ($tariff > 0) {
            $annual_savings = ($monthly_generation * $tariff) * 12;
        }
        $price_per_wp = self::price_per_wp($kwp, $data['price_bands']);
        $price_range = min(40, max(0, self::number($data['price_range_percent']))) / 100;
        $system_price = $kwp * 1000 * $price_per_wp;
        $system_price_min = $system_price * (1 - $price_range);
        $system_price_max = $system_price * (1 + $price_range);
        $simple_payback = ($system_price > 0 && $annual_savings > 0)
            ? $system_price / $annual_savings
            : null;

        $contracted_demand = self::positive($data['contracted_demand']);
        $warnings = array();
        if ($data['customer_type'] === 'commercial_demand' && $contracted_demand <= 0) {
            $warnings[] = __('A demanda contratada não foi informada. Ela não define diretamente o kWp, mas é necessária para validar demanda, inversores e estratégia de conexão.', 'solar-integradores');
        }
        if ($data['customer_type'] === 'commercial_demand' && $contracted_demand > 0 && $kwp > $contracted_demand) {
            $warnings[] = __('A potência fotovoltaica estimada supera numericamente a demanda contratada. É necessária análise de simultaneidade, demanda medida e regras da distribuidora.', 'solar-integradores');
        }

        return array(
            'monthly_consumption_kwh' => round($consumption, 2),
            'availability_kwh' => round($availability, 2),
            'compensable_consumption_kwh' => round($compensable_consumption, 2),
            'hsp' => round($hsp, 3),
            'losses_percent' => round($losses * 100, 2),
            'system_kwp' => round($kwp, 2),
            'monthly_generation_kwh' => round($monthly_generation, 2),
            'annual_generation_kwh' => round($monthly_generation * 12, 2),
            'module_power_w' => round($module_power, 0),
            'module_count' => $module_count,
            'adjustment_factor' => $adjustment_factor === null ? null : round($adjustment_factor, 6),
            'equivalent_peak_kwh' => round($equivalent_peak, 2),
            'contracted_demand_kw' => round($contracted_demand, 2),
            'estimated_system_price' => round($system_price, 2),
            'estimated_system_price_min' => round($system_price_min, 2),
            'estimated_system_price_max' => round($system_price_max, 2),
            'estimated_price_per_wp' => round($price_per_wp, 2),
            'estimated_annual_savings' => round($annual_savings, 2),
            'simple_payback_years' => $simple_payback === null ? null : round($simple_payback, 2),
            'warnings' => $warnings,
            'methodology' => 'Consumo compensável / 30 / (HSP x (1 - perdas))',
        );
    }

    public static function default_price_bands()
    {
        return array(
            array('max_kwp' => 2, 'price_per_wp' => 3.87),
            array('max_kwp' => 4, 'price_per_wp' => 2.84),
            array('max_kwp' => 8, 'price_per_wp' => 2.49),
            array('max_kwp' => 30, 'price_per_wp' => 2.28),
            array('max_kwp' => 75, 'price_per_wp' => 2.10),
            array('max_kwp' => 150, 'price_per_wp' => 1.94),
            array('max_kwp' => 300, 'price_per_wp' => 2.05),
            array('max_kwp' => 999999, 'price_per_wp' => 2.25),
        );
    }

    private static function price_per_wp($kwp, $bands)
    {
        if (!is_array($bands) || !$bands) {
            $bands = self::default_price_bands();
        }

        foreach ($bands as $band) {
            $max_kwp = isset($band['max_kwp']) ? self::positive($band['max_kwp']) : 0;
            $price = isset($band['price_per_wp']) ? self::positive($band['price_per_wp']) : 0;
            if ($max_kwp > 0 && $price > 0 && $kwp <= $max_kwp) {
                return $price;
            }
        }

        $last = end($bands);
        return isset($last['price_per_wp']) ? max(0.01, self::positive($last['price_per_wp'])) : 2.49;
    }

    private static function availability_cost($connection_type)
    {
        $values = array(
            'single_phase' => 30,
            'two_phase' => 50,
            'three_phase' => 100,
        );

        return isset($values[$connection_type]) ? $values[$connection_type] : 100;
    }

    private static function positive($value)
    {
        return max(0, self::number($value));
    }

    private static function number($value)
    {
        if (is_string($value)) {
            $value = str_replace(array(' ', ','), array('', '.'), $value);
        }
        return is_numeric($value) ? (float) $value : 0;
    }
}
