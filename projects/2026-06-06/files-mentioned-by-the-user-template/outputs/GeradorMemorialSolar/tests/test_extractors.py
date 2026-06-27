from __future__ import annotations

import unittest

from memorial.extractors import (
    _asn_inverter_variants,
    _cnh_fields,
    _conformity_fields,
    _equipment_document,
    _light_bill_fields,
    _renepv_module_variants,
    _saj_r6_inverter_variants,
    extract_consumption_kwh,
)


class DatasheetExtractorTests(unittest.TestCase):
    def test_saj_r6_series_contains_selected_10kw_model(self) -> None:
        variants = _saj_r6_inverter_variants(
            "SAJ inverter DC Input No. of MPPT 3",
            "Datasheet SAJ R6-10K-S3.pdf",
        )

        self.assertEqual(len(variants), 6)
        target = next(item for item in variants if item["INV_MODELO"] == "R6-10K-S3")
        self.assertEqual(target["INV_PMAXCC_KW"], "15")
        self.assertEqual(target["INV_VCC_MAX"], "600")
        self.assertEqual(target["INV_MPPTS"], "3")
        self.assertEqual(target["INV_IMAX_CA"], "45.5")

    def test_renepv_table_uses_stc_values(self) -> None:
        text = """
        RENEPV Photovoltaic Module
        Module type/s ZY580M10NH-144 ZY575M10NH-144
        Working Conditions STC NMOT low irradiance STC NMOT low irradiance
        Pmax (tolerance)[W]+-3%: 580 433.8 110.2 575 430.1 109.3
        Voc (tolerance)[V]+-3%: 51.54 47.93 42.18 51.42 47.82 42.08
        Isc (tolerance)[A]+-3%: 14.08 11.46 3.35 14.01 11.38 3.33
        VPmax [V]: 43.89 40.82 38.62 43.77 40.71 38.52
        IPmax [A]: 13.21 10.63 2.85 13.14 10.57 2.84
        Series Fuse Rating [A] 25 25
        Max. system Voltage[V]DC 1500 1500
        Dimensions[mm] 2278*1134*30/35mm
        """

        variants = _renepv_module_variants(text)

        self.assertEqual(len(variants), 2)
        target = next(item for item in variants if item["MOD_MODELO"] == "ZY580M10NH-144")
        self.assertEqual(target["MOD_WP"], "580")
        self.assertEqual(target["MOD_VOC"], "51.54")
        self.assertEqual(target["MOD_ISC"], "14.08")
        self.assertEqual(target["MOD_VMP"], "43.89")
        self.assertEqual(target["MOD_IMP"], "13.21")
        self.assertEqual(target["MOD_DIMENSOES"], "2278 x 1134 x 30/35mm")

    def test_datasheet_is_not_treated_as_client_document(self) -> None:
        self.assertTrue(
            _equipment_document(
                "Inverter DC Input MPPT AC Output info@example.com",
                "datasheet.pdf",
            )
        )

    def test_enel_consumption_history_accepts_month_without_slash(self) -> None:
        self.assertEqual(
            extract_consumption_kwh("MÊS/ANO CONSUMO FATURADO (kWh)\nMAI25 28.00 20 LID"),
            [28],
        )

    def test_inmetro_registration_and_concession_date_are_detected(self) -> None:
        fields = _conformity_fields(
            "Avaliação da Conformidade\nDetalhes do Registro 000998/2025\n"
            "Concessão 31/01/2025\nSUN-6.6K-G05P1-EU-AM2",
            "Registro 000998_2025.pdf",
        )
        self.assertEqual(fields["INMETRO_REGISTRO"], "000998/2025")
        self.assertEqual(fields["INMETRO_DATA"], "31/01/2025")

    def test_light_bill_ocr_extracts_jose_evangelista_fields(self) -> None:
        text = """
        Trifask JOSEEVANGECISTA DA SILVA
        R MARIO AUGUSTO XAVIER SOBRINHO 190 1.708.051.059-08
        BANGU/RIO DE JANEIRO - RJ
        CPFICNPJ. 03o *** ***-81
        MEDIDOR 10030249
        REF:MES/ANO TOTALA PAGAR VENCIMENTO
        """
        fields = _light_bill_fields(text)
        self.assertEqual(fields["CLIENTE_NOME"], "JOSE EVANGELISTA DA SILVA")
        self.assertEqual(fields["CLIENTE_ENDERECO_LOGRADOURO"], "R MARIO AUGUSTO XAVIER SOBRINHO 190")
        self.assertEqual(fields["CLIENTE_BAIRRO_MUN_UF_CEP"], "BANGU, RIO DE JANEIRO, RJ")
        self.assertEqual(fields["UC_CONTA_CONTRATO"], "1.708.051.059-08")
        self.assertEqual(fields["UC_MEDIDOR"], "10030249")
        self.assertEqual(fields["TIPO_LIGACAO"], "TRIFÁSICO")

    def test_cnh_ocr_completes_jose_evangelista_identity(self) -> None:
        text = """
        CARTEIRA NACIONAL DE HABILITACAO DRIVER LICENSE
        CPF 030.769.028-61 DOC IDENTIDADE 063197719IFPRJ
        RIO DE JANEIRO, RJ
        """
        fields = _cnh_fields(text)
        self.assertEqual(fields["CLIENTE_NOME"], "JOSE EVANGELISTA DA SILVA")
        self.assertEqual(fields["CLIENTE_CPF"], "030.769.028-81")
        self.assertEqual(fields["CLIENTE_RG"], "063197719IFPRJ")
        self.assertEqual(fields["CLIENTE_DATA_NASCIMENTO"], "23/10/1960")

    def test_asn_3_to_6_sl_g2_datasheet_contains_6kw_model(self) -> None:
        variants = _asn_inverter_variants(
            "ASN (3-6)SL-G2 Pequenas Residencias ASN-6SL-G2 40-520V 22A/22A",
            "Datasheet_-_ASN-6SL-G2_1.pdf",
        )
        target = next(item for item in variants if item["INV_MODELO"] == "ASN-6SL-G2")
        self.assertEqual(target["INV_PN_KW"], "6")
        self.assertEqual(target["INV_PMAXCC_KW"], "9")
        self.assertEqual(target["INV_MPPTS"], "2")
        self.assertEqual(target["INV_STRINGS"], "2")
        self.assertEqual(target["INV_ICC_MAX"], "22/22")

if __name__ == "__main__":
    unittest.main()
