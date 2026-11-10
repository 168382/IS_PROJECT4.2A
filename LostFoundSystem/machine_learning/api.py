
from flask import Flask, request, jsonify
import pandas as pd
import joblib

from pathlib import Path
from difflib import SequenceMatcher


app = Flask(__name__)

# Model location
BASE_DIR = Path(__file__).resolve().parent
MODEL_PATH = BASE_DIR / "lost_found_model.pkl"


# Load trained model
try:
    model = joblib.load(MODEL_PATH)
    print("Machine learning model loaded successfully!")

except Exception as error:
    model = None
    print(f"Error loading machine learning model: {error}")


# Calculate comparison features
def prepare_features(item, candidate):

    name_similarity = SequenceMatcher(
        None,
        str(item.get("item_name") or "").lower(),
        str(candidate.get("item_name") or "").lower()
    ).ratio()

    description_similarity = SequenceMatcher(
        None,
        str(item.get("description") or "").lower(),
        str(candidate.get("description") or "").lower()
    ).ratio()

    category_match = int(
        str(item.get("category_name") or "").lower().strip()
        == str(candidate.get("category_name") or "").lower().strip()
    )

    color_match = int(
        str(item.get("color") or "").lower().strip()
        == str(candidate.get("color") or "").lower().strip()
    )

    location_match = int(
        str(item.get("location") or "").lower().strip()
        == str(candidate.get("location") or "").lower().strip()
    )

    return pd.DataFrame([{
        "name_similarity": name_similarity,
        "description_similarity": description_similarity,
        "category_match": category_match,
        "color_match": color_match,
        "location_match": location_match
    }])


@app.route("/match", methods=["POST"])
def match_endpoint():

    if model is None:
        return jsonify({
            "error": "Machine learning model is not loaded."
        }), 500

    data = request.get_json(silent=True)

    if not data or "item" not in data or "dataset" not in data:
        return jsonify({
            "error": "Invalid request. Provide item and dataset."
        }), 400

    item = data["item"]
    dataset = pd.DataFrame(data["dataset"])

    if dataset.empty:
        return jsonify({"matches": []})

    matches = []

    for _, candidate in dataset.iterrows():

        candidate_data = candidate.to_dict()

        features = prepare_features(item, candidate_data)

        prediction = int(model.predict(features)[0])

        probabilities = model.predict_proba(features)[0]

        probability = float(probabilities[1]) * 100

        if prediction == 1:
            matches.append({
                "item_id": candidate_data.get("id"),
                "similarity_score": round(probability, 2)
            })

    matches.sort(
        key=lambda result: result["similarity_score"],
        reverse=True
    )

    return jsonify({
        "matches": matches[:5]
    })


if __name__ == "__main__":
    app.run(
        host="127.0.0.1",
        port=5000,
        debug=True
    )
