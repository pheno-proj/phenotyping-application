import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder
import pickle
import os

# Define paths
DATA_PATH     = os.path.join('..', 'assets', 'data', 'xAPI-Edu-Data.csv')
MODEL_DIR     = 'models'
MODEL_PATH    = os.path.join(MODEL_DIR, 'model.pkl')
ENCODER_PATH  = os.path.join(MODEL_DIR, 'encoders.pkl')
FEATURES_PATH = os.path.join(MODEL_DIR, 'feature_names.pkl')

def train():
    if not os.path.exists(DATA_PATH):
        print(f"Error: Dataset not found at {DATA_PATH}")
        return

    # Load data
    df = pd.read_csv(DATA_PATH)

    if 'Class' not in df.columns:
        print("Error: Target column 'Class' not found in dataset.")
        return

    # ── Encode Categorical Features ────────────────────────────────────────
    le_dict = {}
    categorical_cols = [
        'gender', 'NationalITy', 'PlaceofBirth', 'StageID', 'GradeID',
        'SectionID', 'Topic', 'Semester', 'Relation', 'ParentAnsweringSurvey',
        'ParentschoolSatisfaction', 'StudentAbsenceDays'
    ]
    for col in categorical_cols:
        if col in df.columns:
            le = LabelEncoder()
            df[col] = le.fit_transform(df[col].astype(str))
            le_dict[col] = le

    # Encode Target
    le_target = LabelEncoder()
    df['Class'] = le_target.fit_transform(df['Class'])
    le_dict['Class'] = le_target

    # ── Feature Selection: Remove Low-Importance Columns ───────────────────
    X_full = df.drop('Class', axis=1)
    y      = df['Class']

    # Quick forest to compute importances on ALL features
    clf_probe = RandomForestClassifier(n_estimators=100, random_state=42)
    clf_probe.fit(X_full, y)

    importances = pd.Series(clf_probe.feature_importances_, index=X_full.columns)
    print("\n-- Feature Importances ----------------------------------------")
    print(importances.sort_values(ascending=False).to_string())

    # Keep only features with importance >= 4.5% (Focus on strong signals)
    selected_features = importances[importances >= 0.045].index.tolist()
    removed_features  = importances[importances <  0.045].index.tolist()
    print(f"\n[KEPT]    ({len(selected_features)}): {selected_features}")
    print(f"[REMOVED] ({len(removed_features)}): {removed_features}")

    X = X_full[selected_features]

    # ── Train / Test Split ─────────────────────────────────────────────────
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42
    )

    # ── Train Final Model (500 trees, selected features) ───────────────────
    clf = RandomForestClassifier(
        n_estimators=500,      # More trees → higher consensus → higher confidence
        max_depth=10,
        min_samples_split=4,
        min_samples_leaf=2,
        random_state=42,
        n_jobs=-1              # Use all CPU cores for speed
    )
    clf.fit(X_train, y_train)

    accuracy = clf.score(X_test, y_test)
    print(f"\nModel trained. Accuracy on test set: {accuracy:.4f} ({accuracy*100:.2f}%)")

    # ── Persist Everything ─────────────────────────────────────────────────
    if not os.path.exists(MODEL_DIR):
        os.makedirs(MODEL_DIR)

    with open(MODEL_PATH, 'wb') as f:
        pickle.dump(clf, f)

    with open(ENCODER_PATH, 'wb') as f:
        pickle.dump(le_dict, f)

    feature_names = selected_features
    with open(FEATURES_PATH, 'wb') as f:
        pickle.dump(feature_names, f)

    print(f"\nModel saved    -> {MODEL_PATH}")
    print(f"Encoders saved -> {ENCODER_PATH}")
    print(f"Features saved -> {FEATURES_PATH}")
    print(f"Feature list   : {feature_names}")

if __name__ == "__main__":
    train()
