from flask import Flask, request, jsonify
import pandas as pd

# Support both `python api.py` from this directory and package imports from
# the Laravel project root (for tests and deployments).
try:
    from .matching.matcher import match_item
except ImportError:
    from matching.matcher import match_item

app = Flask(__name__)

@app.route('/match', methods=['POST'])
def match_endpoint():
    data = request.json
    if not data or 'item' not in data or 'dataset' not in data:
        return jsonify({'error': 'Invalid request. Provide item and dataset.'}), 400
    
    item = data['item']
    dataset = pd.DataFrame(data['dataset'])
    
    if dataset.empty:
        return jsonify({'matches': []})
    
    matches = match_item(item, dataset)
    
    return jsonify({'matches': matches})

if __name__ == '__main__':
    app.run(port=5000, debug=True)
