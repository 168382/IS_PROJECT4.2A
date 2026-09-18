import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

# The API can be launched as a script or imported as the
# `machine_learning` package. Handle either entry point cleanly.
try:
    from ..preprocessing.cleaner import clean_text
except ImportError:
    from preprocessing.cleaner import clean_text

def combine_features(row):
    features = [
        str(row.get('item_name', '')),
        str(row.get('description', '')),
        str(row.get('category_name', '')),
        str(row.get('color', '')),
        str(row.get('brand', '')),
        str(row.get('location', ''))
    ]
    return " ".join(features)

def match_item(target_item, dataset, top_n=5):
    """
    target_item: dict representing the newly reported item.
    dataset: DataFrame of the existing opposite items (e.g., if target is Lost, dataset is Found items).
    """
    # Keep the caller's DataFrame unchanged; the endpoint may reuse it.
    dataset = dataset.copy()

    # 1. Combine features for the target item
    target_combined = combine_features(target_item)
    target_cleaned = clean_text(target_combined)
    
    # 2. Combine features for the dataset
    dataset['combined_text'] = dataset.apply(combine_features, axis=1)
    dataset['cleaned_text'] = dataset['combined_text'].apply(clean_text)
    
    # 3. Vectorization
    vectorizer = TfidfVectorizer()
    # Fit on dataset + target to ensure all vocabulary is covered
    all_texts = dataset['cleaned_text'].tolist() + [target_cleaned]
    if not any(text.strip() for text in all_texts):
        return []

    try:
        tfidf_matrix = vectorizer.fit_transform(all_texts)
    except ValueError:
        return []
    
    # Target is the last row in the tfidf_matrix
    target_vector = tfidf_matrix[-1]
    dataset_vectors = tfidf_matrix[:-1]
    
    # 4. Cosine Similarity
    similarities = cosine_similarity(target_vector, dataset_vectors).flatten()
    
    # 5. Rank
    dataset['similarity_score'] = (similarities * 100).round(2) # Convert to percentage
    
    # Filter matches > 0 and sort
    matches = dataset[dataset['similarity_score'] > 0]
    matches = matches.sort_values(by='similarity_score', ascending=False).head(top_n)
    
    # Format output
    results = []
    for _, row in matches.iterrows():
        results.append({
            'item_id': row.get('id'),
            'item_name': row.get('item_name'),
            'similarity_score': row['similarity_score']
        })
        
    return results
