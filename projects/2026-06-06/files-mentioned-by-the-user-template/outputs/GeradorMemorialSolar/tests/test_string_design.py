import unittest

from memorial.string_design import design_string_arrangement


class StringDesignTests(unittest.TestCase):
    def test_saj_25kw_uses_two_equal_strings_per_mppt(self):
        design = design_string_arrangement(
            {
                "MOD_QTD": "40",
                "MOD_WP": "600",
                "MOD_VOC": "55.05",
                "MOD_VMP": "46.77",
                "MOD_ISC": "13.76",
                "INV_VCC_MAX": "1100",
                "INV_VMPPT_MIN": "180",
                "INV_VMPPT_MAX": "1000",
                "INV_VSTART": "200",
                "INV_ICC_MAX": "32",
                "INV_PMAXCC_KW": "50",
                "INV_MPPTS": "4",
                "INV_STRINGS": "8",
            }
        )

        self.assertEqual(
            [(item.label, item.modules) for item in design.strings],
            [
                ("1/1", 5),
                ("1/2", 5),
                ("2/1", 5),
                ("2/2", 5),
                ("3/1", 5),
                ("3/2", 5),
                ("4/1", 5),
                ("4/2", 5),
            ],
        )
        self.assertEqual(design.warnings, [])

    def test_remainder_is_isolated_in_next_mppt(self):
        design = design_string_arrangement(
            {
                "MOD_QTD": "34",
                "MOD_WP": "600",
                "MOD_VOC": "55.05",
                "MOD_VMP": "46.77",
                "MOD_ISC": "13.76",
                "INV_VCC_MAX": "1100",
                "INV_VMPPT_MIN": "180",
                "INV_VMPPT_MAX": "1000",
                "INV_VSTART": "200",
                "INV_ICC_MAX": "32",
                "INV_PMAXCC_KW": "50",
                "INV_MPPTS": "4",
                "INV_STRINGS": "8",
            }
        )

        self.assertEqual([item.modules for item in design.strings], [5, 5, 5, 5, 5, 9])
        self.assertEqual(design.strings[-1].label, "4/1")
        self.assertTrue(any("quantidade diferente" in warning for warning in design.warnings))


if __name__ == "__main__":
    unittest.main()
