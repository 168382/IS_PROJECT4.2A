import re
from nltk.corpus import stopwords
from nltk.stem import WordNetLemmatizer
from nltk.tokenize import word_tokenize

# Use optional NLTK corpora when they are installed. The matching API must not
# attempt network downloads at import time because that makes startup fragile.
try:
    stop_words = set(stopwords.words('english'))
except LookupError:
    stop_words = set()

lemmatizer = WordNetLemmatizer()

def clean_text(text):
    """
    Cleans text by:
    1. Lowercasing
    2. Removing non-alphanumeric characters
    3. Tokenization
    4. Stop-word removal
    5. Lemmatization
    """
    if not isinstance(text, str):
        return ""
        
    # Lowercase
    text = text.lower()
    
    # Remove special characters and numbers (keep only letters)
    text = re.sub(r'[^a-z\s]', '', text)
    
    # Tokenization
    # preserve_line avoids NLTK's Punkt sentence-model dependency.
    tokens = word_tokenize(text, preserve_line=True)
    
    # Stop-word removal and Lemmatization
    cleaned_tokens = []
    for word in tokens:
        if word in stop_words:
            continue

        try:
            cleaned_tokens.append(lemmatizer.lemmatize(word))
        except LookupError:
            # Matching remains available when the optional WordNet corpus is absent.
            cleaned_tokens.append(word)
    
    return " ".join(cleaned_tokens)
