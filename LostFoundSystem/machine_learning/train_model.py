
import pandas as pd
import joblib

from pathlib import Path
from difflib import SequenceMatcher

from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.linear_model import LogisticRegression
from sklearn.pipeline import Pipeline
from sklearn.compose import ColumnTransformer
from sklearn.preprocessing import FunctionTransformer


# Project paths
BASE_DIR = Path(__file__).resolve().parent
DATASET_PATH = BASE_DIR / "datasets" / "training_data.csv"
MODEL_PATH = BASE_DIR / "lost_found_model.pkl"

print("\n===== LOST AND FOUND MODEL TRAINING =====")

# Load dataset
data = pd.read_csv(DATASET_PATH)

print("Dataset loaded successfully!")
print("Total records:", len(data))


# Required columns
required_columns = [
    "lost_name", "found_name",
    "lost_description", "found_description",
    "lost_category", "found_category",
    "lost_color", "found_color",
    "lost_location", "found_location",
    "same_item"
]

missing = set(required_columns) - set(data.columns)

if missing:
    raise ValueError(f"Missing columns: {missing}")

if data["same_item"].nunique() != 2:
    raise ValueError("Dataset must contain both labels 0 and 1.")


# Clean missing values
text_columns = required_columns[:-1]

data[text_columns] = data[text_columns].fillna("").astype(str)


# Calculate similarity features
def create_features(df):

    features = []

    for _, row in df.iterrows():

        name_similarity = SequenceMatcher(
            None,
            row["lost_name"].lower(),
            row["found_name"].lower()
        ).ratio()

        description_similarity = SequenceMatcher(
            None,
            row["lost_description"].lower(),
            row["found_description"].lower()
        ).ratio()

        category_match = int(
            row["lost_category"].lower().strip()
            == row["found_category"].lower().strip()
        )

        color_match = int(
            row["lost_color"].lower().strip()
            == row["found_color"].lower().strip()
        )

        location_match = int(
            row["lost_location"].lower().strip()
            == row["found_location"].lower().strip()
        )

        features.append([
            name_similarity,
            description_similarity,
            category_match,
            color_match,
            location_match
        ])

    return pd.DataFrame(
        features,
        columns=[
            "name_similarity",
            "description_similarity",
            "category_match",
            "color_match",
            "location_match"
        ]
    )


# Generate training features
X = create_features(data)
y = data["same_item"].astype(int)


# Train classifier
model = Pipeline([
    (
        "classifier",
        LogisticRegression(
            max_iter=1000,
            class_weight="balanced",
            random_state=42
        )
    )
])

model.fit(X, y)


# Save model
joblib.dump(model, MODEL_PATH)

print("\nTraining completed successfully!")
print("Model saved successfully!")
print("Model location:", MODEL_PATH)

print("\nLabel distribution:")
print(data["same_item"].value_counts().sort_index())

print("\nTraining feature preview:")
print(X.head())

print("\n===== TRAINING FINISHED =====")
