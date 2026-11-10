
import pandas as pd
import numpy as np
import matplotlib.pyplot as plt

from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.metrics import (
    confusion_matrix,
    ConfusionMatrixDisplay,
    classification_report,
    accuracy_score,
    precision_score,
    recall_score,
    f1_score
)

# 1. Load dataset
df = pd.read_csv("machine_learning/datasets/training_data.csv")

# 2. Prepare lost item descriptions
lost_text = (
    df["lost_name"].fillna("") + " " +
    df["lost_description"].fillna("") + " " +
    df["lost_category"].fillna("") + " " +
    df["lost_color"].fillna("") + " " +
    df["lost_location"].fillna("")
)

# 3. Prepare found item descriptions
found_text = (
    df["found_name"].fillna("") + " " +
    df["found_description"].fillna("") + " " +
    df["found_category"].fillna("") + " " +
    df["found_color"].fillna("") + " " +
    df["found_location"].fillna("")
)

# 4. Convert text into TF-IDF vectors
vectorizer = TfidfVectorizer()
all_text = pd.concat([lost_text, found_text])

vectors = vectorizer.fit_transform(all_text)

# 5. Calculate similarity scores
n = len(df)
predicted_scores = []

for i in range(n):
    lost_vector = vectors[i]
    found_vector = vectors[n + i]

    score = cosine_similarity(
        lost_vector, found_vector
    )[0][0]

    predicted_scores.append(score)

# 6. Generate actual and predicted labels
threshold = 0.30

y_pred = [
    1 if score >= threshold else 0
    for score in predicted_scores
]

y_actual = df["same_item"].astype(int)

# 7. Confusion matrix
cm = confusion_matrix(y_actual, y_pred, labels=[0, 1])

print("\nCONFUSION MATRIX")
print(cm)

print("\nCLASSIFICATION REPORT")
print(classification_report(
    y_actual,
    y_pred,
    labels=[0, 1],
    target_names=["Not a Match", "Match"],
    zero_division=0
))

# 8. Display and save confusion matrix
display = ConfusionMatrixDisplay(
    confusion_matrix=cm,
    display_labels=["Not a Match", "Match"]
)

display.plot(cmap="Blues")
plt.title("Lost and Found Matching - Confusion Matrix")
plt.savefig(
    "machine_learning/confusion_matrix.png",
    dpi=300,
    bbox_inches="tight"
)
plt.show()

# 9. Calculate evaluation metrics
accuracy = accuracy_score(y_actual, y_pred)
precision = precision_score(y_actual, y_pred, zero_division=0)
recall = recall_score(y_actual, y_pred, zero_division=0)
f1 = f1_score(y_actual, y_pred, zero_division=0)

metrics = {
    "Accuracy": accuracy * 100,
    "Precision": precision * 100,
    "Recall": recall * 100,
    "F1-score": f1 * 100
}

print("\nEVALUATION METRICS")
for name, value in metrics.items():
    print(f"{name}: {value:.2f}%")

# 10. Evaluation metrics graph
plt.figure(figsize=(9, 5))
plt.bar(metrics.keys(), metrics.values())
plt.ylabel("Score (%)")
plt.ylim(0, 100)
plt.title("Lost and Found Matching - Evaluation Metrics")
plt.savefig(
    "machine_learning/evaluation_metrics.png",
    dpi=300,
    bbox_inches="tight"
)
plt.show()

# 11. Actual vs Predicted graph
plt.figure(figsize=(9, 5))
x = np.arange(len(y_actual))

plt.plot(x, y_actual, marker="o", label="Actual")
plt.plot(x, y_pred, marker="x", label="Predicted")

plt.xlabel("Testing Pair")
plt.ylabel("Classification")
plt.yticks([0, 1], ["Not a Match", "Match"])
plt.title("Actual vs Predicted Matching Results")
plt.legend()
plt.grid(True)

plt.savefig(
    "machine_learning/actual_vs_predicted.png",
    dpi=300,
    bbox_inches="tight"
)
plt.show()

print("\nEvaluation completed successfully!")
