"""
Generate a synthetic dataset matching the xAPI-Edu-Data schema.
Run this script once to produce the CSV file used by train_model.py.
"""
import pandas as pd
import random
import os

random.seed(42)

TOPICS = ['IT', 'Math', 'Arabic', 'Science', 'English', 'Quran', 'Spanish', 'French', 'History', 'Biology']
STAGES = ['lowerlevel', 'MiddleSchool', 'HighSchool']
GRADES = ['G-02', 'G-04', 'G-05', 'G-06', 'G-07', 'G-08', 'G-09', 'G-10', 'G-11', 'G-12']
SECTIONS = ['A', 'B', 'C']
GENDERS = ['M', 'F']
NATIONALITIES = ['KW', 'lebanon', 'Egypt', 'SaudiArabia', 'USA', 'Jordan', 'venzuela', 'Iran', 'Tunis', 'Morocco']
SEMESTERS = ['First', 'Second']
RELATIONS = ['Father', 'Mum']
PARENT_SURVEY = ['Yes', 'No']
PARENT_SATISFACTION = ['Good', 'Bad']
ABSENCE = ['Under-7', 'Above-7']

rows = []
for _ in range(600):
    raised = random.randint(0, 100)
    visited = random.randint(0, 100)
    announcements = random.randint(0, 100)
    discussion = random.randint(0, 100)
    absence = random.choice(ABSENCE)
    survey = random.choice(PARENT_SURVEY)
    satisfaction = random.choice(PARENT_SATISFACTION)

    # Determine class based on realistic heuristics
    score = raised + visited + announcements + discussion
    penalty = (20 if absence == 'Above-7' else 0) + (10 if survey == 'No' else 0) + (5 if satisfaction == 'Bad' else 0)
    score -= penalty

    if score > 220:
        cls = 'H'
    elif score > 130:
        cls = 'M'
    else:
        cls = 'L'

    rows.append({
        'gender': random.choice(GENDERS),
        'NationalITy': random.choice(NATIONALITIES),
        'PlaceofBirth': random.choice(NATIONALITIES),
        'StageID': random.choice(STAGES),
        'GradeID': random.choice(GRADES),
        'SectionID': random.choice(SECTIONS),
        'Topic': random.choice(TOPICS),
        'Semester': random.choice(SEMESTERS),
        'Relation': random.choice(RELATIONS),
        'raisedhands': raised,
        'VisITedResources': visited,
        'AnnouncementsView': announcements,
        'Discussion': discussion,
        'ParentAnsweringSurvey': survey,
        'ParentschoolSatisfaction': satisfaction,
        'StudentAbsenceDays': absence,
        'Class': cls
    })

df = pd.DataFrame(rows)
out_dir = os.path.join('..', 'assets', 'data')
os.makedirs(out_dir, exist_ok=True)
out_path = os.path.join(out_dir, 'xAPI-Edu-Data.csv')
df.to_csv(out_path, index=False)
print(f"Dataset generated: {out_path} ({len(df)} records)")
print(df['Class'].value_counts().to_dict())
