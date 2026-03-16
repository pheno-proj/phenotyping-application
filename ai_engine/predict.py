import pandas as pd
import pickle
import sys
import json
import os
import warnings
# warnings.filterwarnings("ignore", category=UserWarning) # Suppress version warnings from polluting JSON output

# Define paths
MODEL_DIR     = 'models'
MODEL_PATH    = os.path.join(os.path.dirname(__file__), MODEL_DIR, 'model.pkl')
ENCODER_PATH  = os.path.join(os.path.dirname(__file__), MODEL_DIR, 'encoders.pkl')
FEATURES_PATH = os.path.join(os.path.dirname(__file__), MODEL_DIR, 'feature_names.pkl')

def predict(input_json):
    if not os.path.exists(MODEL_PATH) or not os.path.exists(ENCODER_PATH):
        return json.dumps({"error": "Model or encoders not found. Please train the model first."})

    try:
        # Load model, encoders, and feature order
        with open(MODEL_PATH, 'rb') as f:
            clf = pickle.load(f)
        
        with open(ENCODER_PATH, 'rb') as f:
            le_dict = pickle.load(f)

        with open(FEATURES_PATH, 'rb') as f:
            feature_names = pickle.load(f)

        # Parse input
        data = json.loads(input_json)
        
        # Prepare DataFrame
        df = pd.DataFrame([data])
        
        # Apply label encoding to categorical columns
        for col, le in le_dict.items():
            if col != 'Class' and col in df.columns:
                try:
                    df[col] = le.transform(df[col].astype(str))
                except ValueError:
                    df[col] = 0  # Fallback for unseen labels

        # Ensure all training columns are present (fill missing with 0)
        for col in feature_names:
            if col not in df.columns:
                df[col] = 0

        # ⚠️ CRITICAL: Reorder columns to exactly match training order
        df = df[feature_names]
        
        # Predict
        prediction_encoded = clf.predict(df)
        prediction_class = le_dict['Class'].inverse_transform(prediction_encoded)[0]
        
        # Confidence
        proba = clf.predict_proba(df).max()

        result = {
            "prediction": prediction_class,
            "confidence": round(proba * 100, 2),
            "status": "success"
        }
        
    except Exception as e:
        result = {
            "error": str(e),
            "status": "failed"
        }

    return json.dumps(result)

if __name__ == "__main__":
    if len(sys.argv) > 1:
        arg = sys.argv[1]
        # If argument is a file path, read JSON from file (called from PHP via temp file)
        if os.path.isfile(arg):
            with open(arg, 'r', encoding='utf-8') as f:
                input_json = f.read()
        else:
            # Treat argument as raw JSON string (called from CLI / test)
            input_json = arg
        print(predict(input_json))
    else:
        print(json.dumps({"error": "No input data provided"}))
