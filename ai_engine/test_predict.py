import subprocess, json

data = {
    "gender": "M",
    "NationalITy": "KW",
    "PlaceofBirth": "KuwaIT",
    "StageID": "MiddleSchool",
    "GradeID": "G-07",
    "SectionID": "A",
    "Topic": "IT",
    "Semester": "F",
    "Relation": "Father",
    "raisedhands": 80,
    "VisITedResources": 90,
    "AnnouncementsView": 60,
    "Discussion": 70,
    "ParentAnsweringSurvey": "Yes",
    "ParentschoolSatisfaction": "Good",
    "StudentAbsenceDays": "Under-7"
}

result = subprocess.run(
    ["python", "predict.py", json.dumps(data)],
    capture_output=True, text=True
)
print("STDOUT:", result.stdout)
print("STDERR:", result.stderr)
