
import unittest
from pathlib import Path

import joblib
import pandas as pd

from machine_learning.matching.matcher import match_item
from machine_learning.preprocessing.cleaner import clean_text


class MatcherTest(unittest.TestCase):

    def test_clean_text_handles_missing_nltk_corpora_and_non_string_values(self):
        self.assertEqual(clean_text(None), "")
        self.assertIn("blue", clean_text("The BLUE backpack #42!"))

    def test_match_item_returns_the_most_relevant_candidate(self):
        dataset = pd.DataFrame([
            {
                "id": "match-1",
                "item_name": "Blue backpack",
                "description": "Jansport bag",
                "color": "blue"
            },
            {
                "id": "other-1",
                "item_name": "Car keys",
                "description": "keyring",
                "color": "silver"
            },
        ])

        matches = match_item(
            {
                "item_name": "Blue Jansport backpack",
                "description": "laptop bag",
                "color": "blue"
            },
            dataset,
        )

        self.assertEqual(matches[0]["item_id"], "match-1")
        self.assertNotIn("combined_text", dataset.columns)

    def test_match_item_returns_no_match_for_empty_text(self):
        matches = match_item(
            {"item_name": ""},
            pd.DataFrame([
                {"id": "empty", "item_name": ""}
            ])
        )

        self.assertEqual(matches, [])

    def test_trained_model_can_predict_item_match(self):
        model_path = (
            Path(__file__).resolve().parents[1]
            / "lost_found_model.pkl"
        )

        self.assertTrue(
            model_path.exists(),
            "Trained model file was not found."
        )

        model = joblib.load(model_path)

        test_item = (
            "Blue backpack Blue Jansport laptop bag "
            "Bags Blue Library"
        )

        prediction = model.predict([test_item])

        self.assertIn(int(prediction[0]), [0, 1])


if __name__ == "__main__":
    unittest.main(verbosity=2)
