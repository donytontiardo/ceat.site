"""The built-in questions, loaded with `python manage.py load_questions`.

The question counts match the grading tables in scoring.py (a section's answer slots
over its questions): 32 visual, 20 numerical, 33 verbal, 32 sequential, 14 spatial
(5 answers each), 30 3D, 18 system (12 answers each), 40 vocabulary, 30 figurework.

The six picture sections use the images in static/images/<section>/; their answers
were worked out from the images, as the original answer key was lost with the PHP
site's database. REVIEW lists the ones that are a judgement call: check them first.
Verbal, vocabulary and figurework were written anew, in English.
"""

SECTION_INFO = {
    "visual": "Picture choice",
    "numerical": "Type a number",
    "verbal": "Multiple choice",
    "sequential": "Type letters",
    "spatial": "Yes / no",
    "3d": "Type the answer",
    "system": "Type letters",
    "vocabulary": "Multiple choice",
    "figurework": "Type a number",
}

# ── picture sections: the answer per question number ───────────────────────
ODD_ONE_OUT = "Find the one that is most <i>different</i>."
NEXT_SHAPE = "Choose the shape that comes next, in place of the question mark."

# questions with a main image (static/images/visual/<n>.jpg) ask for the next shape
VISUAL = {
    1: "4", 2: "2", 3: "1", 4: "4", 5: "4", 6: "3", 7: "3", 8: "1",
    9: "4", 10: "3", 11: "2", 12: "1", 13: "4", 14: "4", 15: "4", 16: "1",
    17: "4", 18: "4", 19: "1", 20: "2", 21: "4", 22: "1", 23: "1", 24: "3",
    25: "2", 26: "2", 27: "3", 28: "2", 29: "1", 30: "4", 31: "3", 32: "4",
}

NUMERICAL = [1, 8, 3, 2, 5, 18, 3, 15, 39, 20, 74, 29, 19, 2, 16, 9, 28, 11, 31, 9]

SEQUENTIAL = [
    "ED", "CD", "AC", "CF", "DE", "EF", "CE", "DA", "BG", "FG", "BD", "AI", "HI", "IA", "DE", "GH",
    "II", "GA", "FB", "HG", "HC", "EG", "KA", "JK", "PC", "II", "EH", "II", "CC", "BE", "II", "II",
]

SPATIAL = [
    "YNNYN", "YNNYN", "YNNYN", "YYYYY", "YYNYY", "YYNNY", "YYYNY",
    "YNYYY", "YNYYN", "YYYYY", "NYNYY", "YYYNN", "NNYYY", "YNYYY",
]

# ten figures, each asked from side A, B and C in turn
THREE_D = [
    "iii", "i", "iv",  "v", "i", "v",  "iii", "i", "i",  "ii", "i", "ii",  "iv", "ii", "v",
    "iii", "i", "ii",  "v", "iii", "i",  "iii", "iii", "iii",  "v", "iv", "iv",  "i", "iii", "iv",
]

SYSTEM = [
    "GFHKKBJDLGAC", "HIJKLTUVWXAB", "DHGIBLKELIFD", "QFGOHIJKLNGR", "FJAHLKEABHDG", "GFHKKBJDLGAC",
    "DHGIBLKELIFD", "FJAHLKEABHDG", "HFIBLDILABJH", "NRAABUQCMXOJ", "HIJKLTUVWXAB", "QFGOHIJKLNGR",
    "NRAABUQCMXOJ", "GFHKKBJDLGAC", "HFIBLDILABJH", "QFGOHIJKLNGR", "DHGIBLKELIFD", "HIJKLTUVWXAB",
]

# (section, number): why the answer is uncertain
REVIEW = {
    ("visual", 5): "odd one out: chosen for parallel sides / right angles",
    ("visual", 10): "how the shading carries on is open to more than one reading",
    ("visual", 23): "odd one out: chosen to match visual 19 (inner shape with more sides)",
    ("visual", 27): "odd one out: no clear rule found",
    ("visual", 32): "odd one out: no clear rule found",
    ("sequential", 16): "rule partly guessed",
    ("sequential", 17): "rule partly guessed",
    ("sequential", 20): "rule partly guessed",
    ("sequential", 24): "no clear rule found",
    ("sequential", 25): "the shading of answer 1 is a guess",
    ("sequential", 27): "rule partly guessed",
    ("spatial", 4): "answers 2 and 3 are close calls",
    ("spatial", 7): "answers 4 and 5 are close calls",
    ("spatial", 10): "all five look alike; a small change may have been missed",
    ("spatial", 11): "answers 1, 2 and 4 are close calls",
    ("spatial", 12): "answers 4 and 5 are close calls",
    **{("3d", n): "hidden-side views: worked out from the drawing, not certain" for n in range(1, 31)},
}

# ── text sections: (question, answer, choices) ─────────────────────────────
VERBAL = [
    ("Arga is further north than Bima, but not as far north as Cendana. "
     "Which town is furthest south?",
     "2", ["Arga", "Bima", "Cendana", "Arga and Bima are equal", "Cannot be determined"]),
    ("Rina is older than Sari. Tono is younger than Sari. Who is the oldest?",
     "1", ["Rina", "Sari", "Tono", "Sari and Tono", "Cannot be determined"]),
    ("All the cats in Mr Hadi's house are black. Mimi is a cat in Mr Hadi's house. "
     "Which conclusion is correct?",
     "2", ["Mimi is white", "Mimi is black", "Mimi is not a cat",
           "All black cats belong to Mr Hadi", "Mimi lives in another house"]),
    ("Shop A opens earlier than shop B. Shop C opens later than shop B. "
     "Shop D opens earliest. Which shop opens latest?",
     "3", ["Shop A", "Shop B", "Shop C", "Shop D", "Shops A and D"]),
    ("Dewi, Eka and Fajar sit in a row. Dewi sits to the left of Eka and Fajar "
     "sits to the right of Eka. Who sits in the middle?",
     "2", ["Dewi", "Eka", "Fajar", "Dewi and Fajar", "Nobody"]),
    ("If it rains, the road gets wet. This morning the road is not wet. Which conclusion is correct?",
     "2", ["It rained this morning", "It did not rain this morning", "The road is always wet",
           "Rain makes the road dry", "It will rain tomorrow"]),
    ("Building P is taller than building Q. Building R is shorter than building Q. "
     "Building S is taller than building P. Which building is the shortest?",
     "3", ["Building P", "Building Q", "Building R", "Building S", "Buildings Q and R"]),
    ("Ali runs faster than Banu, but slower than Candra. "
     "Dodi runs slower than Banu. Who is the slowest?",
     "4", ["Ali", "Banu", "Candra", "Dodi", "Ali and Banu"]),
    ("A book costs more than a pencil. A ruler costs less than a pencil. "
     "A bag costs more than a book. Which item is the second cheapest?",
     "2", ["Book", "Pencil", "Ruler", "Bag", "Book and bag"]),
    ("All members of the chess club are in year 11. Budi is not in year 11. "
     "Which conclusion is correct?",
     "2", ["Budi is a member of the chess club", "Budi is not a member of the chess club", "Budi is in year 10",
           "All year 11 students are in the chess club", "Budi is in year 12"]),
    ("Train A leaves at 07:00 and arrives 3 hours later. Train B leaves at 08:00 and "
     "arrives 1 hour earlier than train A. How long is train B's journey?",
     "1", ["1 hour", "2 hours", "3 hours", "4 hours", "5 hours"]),
    ("Five people are queuing. Lina is in front of Maya, Maya is in front of Oki, Nina is behind Oki, "
     "and Pita is last. Who is first?",
     "1", ["Lina", "Maya", "Nina", "Oki", "Pita"]),
    ("In a race, Kiki finished first. Gita finished before Hana. Indra finished after "
     "Hana but before Joko. Who finished third?",
     "2", ["Gita", "Hana", "Indra", "Joko", "Kiki"]),
    ("The school is east of the market. The mosque is west of the market. "
     "The post office is east of the school. Which building is furthest west?",
     "3", ["School", "Market", "Mosque", "Post office", "School and market"]),
    ("Anyone who passes the exam gets a certificate. Rudi got a certificate. "
     "Which conclusion is correct?",
     "3", ["Rudi certainly passed the exam", "Rudi did not pass the exam", "Rudi did not necessarily pass the exam",
           "Rudi did not take the exam", "Everyone got a certificate"]),
    ("A father is now 3 times as old as Tika. In five years he will be 2 times as old as Tika. "
     "How old is Tika now?",
     "1", ["5 years", "10 years", "15 years", "20 years", "25 years"]),
    ("Yudi is older than Wawan, Wawan is older than Zaki, and Xena is younger than Zaki. "
     "Who is the second youngest?",
     "3", ["Yudi", "Wawan", "Zaki", "Xena", "Wawan and Zaki"]),
    ("All birds lay eggs. A penguin is a bird. Penguins cannot fly. "
     "Which conclusion is correct?",
     "2", ["Penguins can fly", "Penguins lay eggs", "Everything that lays eggs is a bird",
           "Birds cannot fly", "Penguins are not birds"]),
    ("Kenari is 40 km north of Lontar. Melati is 25 km south of "
     "Kenari, on the same road. How far is Melati from Lontar?",
     "1", ["15 km", "25 km", "40 km", "65 km", "5 km"]),
    ("Team Red beat Team Blue, Team Blue beat Team Green, and Team Green beat "
     "Team Yellow. If the winner is always stronger, which team is the strongest?",
     "1", ["Team Red", "Team Blue", "Team Green", "Team Yellow", "Teams Blue and Green"]),
    ("The market is busier on Monday than on Tuesday. Wednesday is quieter than "
     "Tuesday, and Thursday is busier than Monday. On which day is the market quietest?",
     "3", ["Monday", "Tuesday", "Wednesday", "Thursday", "Monday and Tuesday"]),
    ("Ani, Beni and Cita each keep one pet: a cat, a dog or a fish. "
     "Ani does not keep the cat. Beni keeps the fish. Who keeps the cat?",
     "3", ["Ani", "Beni", "Cita", "Ani and Cita", "Nobody"]),
    ("The red light stays on longer than the yellow light. The green light stays on longer "
     "than the red light. Which light is on for the shortest time?",
     "2", ["Red", "Yellow", "Green", "Red and green", "All the same"]),
    ("Warehouse A holds twice as many items as warehouse B. Warehouse C holds half as many "
     "items as warehouse B. If warehouse C holds 30 items, how many does warehouse A hold?",
     "4", ["30", "60", "90", "120", "150"]),
    ("Everyone in Sukamaju village is either a farmer or a trader, not both. Mr Darto lives "
     "in Sukamaju and is not a farmer. Which conclusion is correct?",
     "2", ["Mr Darto is a farmer", "Mr Darto is a trader", "Mr Darto is a farmer and a trader",
           "Mr Darto does not work", "Mr Darto does not live in Sukamaju"]),
    ("Four houses stand in a row from east to west. Fani's house is at the east end and Hadi's "
     "house is at the west end. Eko's house is between Fani's and Gani's. Whose house is next "
     "to Hadi's?",
     "3", ["Eko", "Fani", "Gani", "Eko and Fani", "Nobody"]),
    ("At 09:00 the office clock shows 09:10. The clock gains 2 minutes "
     "every hour. What time does it show at 12:00?",
     "4", ["12:10", "12:12", "12:14", "12:16", "12:18"]),
    ("A bookcase has 4 shelves, one kind of book per shelf. Maths books are on the top shelf. "
     "History books are above science books, and language books are below science books. "
     "Which books are on the bottom shelf?",
     "3", ["History", "Science", "Language", "Maths", "History and science"]),
    ("No fish can live on land. All catfish are fish. Which conclusion is correct?",
     "3", ["Catfish can live on land", "Some catfish live on land", "Catfish cannot live on land",
           "Everything that lives in water is a catfish", "Fish can live on land"]),
    ("Fikri scored higher than Gina, but lower than Hani. "
     "Ivan scored the same as Gina. Who scored highest?",
     "3", ["Fikri", "Gina", "Hani", "Ivan", "Gina and Ivan"]),
    ("A car travels 60 km in 1 hour. Another car covers the same distance in 40 minutes. "
     "How fast is the second car?",
     "4", ["60 km/h", "75 km/h", "80 km/h", "90 km/h", "120 km/h"]),
    ("If Dina goes to the party, Evi goes too. If Evi goes, Fira does not go. Dina goes to the party. "
     "Which conclusion is correct?",
     "3", ["Evi does not go", "Fira goes", "Fira does not go", "Dina and Fira go", "Nobody goes"]),
    ("A bus leaves the stop every 15 minutes, starting at 06:00. Rani reaches the stop at 06:50. "
     "When does the next bus leave?",
     "3", ["06:45", "06:55", "07:00", "07:05", "07:15"]),
]

# the word with the closest meaning
VOCABULARY = [
    ("Possess", "1", ["Own", "Pay", "Surrender", "Release"]),
    ("Clever", "2", ["Lazy", "Smart", "Slow", "Friendly"]),
    ("Persistent", "3", ["Hesitant", "Weak", "Determined", "Quick"]),
    ("Sturdy", "4", ["Fragile", "Light", "Flexible", "Strong"]),
    ("Frugal", "1", ["Thrifty", "Wasteful", "Lavish", "Generous"]),
    ("Customary", "2", ["Strange", "Usual", "Rare", "New"]),
    ("Valid", "3", ["Fake", "Doubtful", "Sound", "Weak"]),
    ("Resilient", "4", ["Feeble", "Spoiled", "Slow", "Tough"]),
    ("Lovely", "1", ["Beautiful", "Ugly", "Dirty", "Rough"]),
    ("Transient", "2", ["Eternal", "Temporary", "Everlasting", "Real"]),
    ("Pristine", "3", ["Filthy", "Crowded", "Spotless", "Dark"]),
    ("Candid", "4", ["Evasive", "Complicated", "Vague", "Frank"]),
    ("Agile", "1", ["Nimble", "Sluggish", "Unsure", "Idle"]),
    ("Consensus", "2", ["Dispute", "Agreement", "Denial", "Refusal"]),
    ("Foolish", "3", ["Shrewd", "Bright", "Silly", "Sly"]),
    ("Ambiguous", "4", ["Clear", "Firm", "Certain", "Unclear"]),
    ("Medium", "1", ["Means", "Obstacle", "Goal", "Result"]),
    ("Visit", "2", ["Contest", "Call", "Farewell", "Hideout"]),
    ("Aware", "3", ["Forgetful", "Angry", "Conscious", "Confused"]),
    ("Constraint", "4", ["Opportunity", "Help", "Reward", "Limitation"]),
    ("Vessel", "1", ["Ship", "House", "Train", "Bridge"]),
    ("Expert", "2", ["Beginner", "Specialist", "Pupil", "Spectator"]),
    ("Hazardous", "3", ["Safe", "Easy", "Dangerous", "Cheap"]),
    ("Self-sufficient", "4", ["Imported", "Dependent", "Lacking", "Independent"]),
    ("Relative", "1", ["Comparative", "Absolute", "Certain", "Fixed"]),
    ("Tentative", "2", ["Definite", "Provisional", "Permanent", "Final"]),
    ("Explicit", "3", ["Implied", "Vague", "Stated", "Hidden"]),
    ("Paradigm", "4", ["Mistake", "Habit", "Decision", "Model"]),
    ("Distortion", "1", ["Misrepresentation", "Repair", "Reinforcement", "Explanation"]),
    ("Prestige", "2", ["Loss", "Status", "Poverty", "Weakness"]),
    ("Contemporary", "3", ["Ancient", "Classical", "Modern", "Traditional"]),
    ("Essence", "4", ["Surface", "Extra", "Decoration", "Core"]),
    ("Absurd", "1", ["Ridiculous", "Reasonable", "Sensible", "Ordinary"]),
    ("Mitigate", "2", ["Increase", "Lessen", "Ignore", "Spread"]),
    ("Synergy", "3", ["Rivalry", "Division", "Cooperation", "Conflict"]),
    ("Deficit", "4", ["Excess", "Profit", "Surplus", "Shortfall"]),
    ("Consequence", "1", ["Outcome", "Cause", "Start", "Purpose"]),
    ("Commonplace", "2", ["Odd", "Ordinary", "Scarce", "Exceptional"]),
    ("Intricate", "3", ["Easy", "Simple", "Complex", "Light"]),
    ("Restore", "4", ["Damage", "Destroy", "Dismantle", "Repair"]),
]

# answered as the instructions say: decimals with a point and at least two decimals,
# no units or % signs, and a fraction in its simplest form when one is asked for
FIGUREWORK = [
    ("What is 1/4 as a decimal?", "0.25"),
    ("What is 15% of 200?", "30"),
    ("What is 12.5 + 7.75?", "20.25"),
    ("Write 3/5 as a decimal.", "0.60"),
    ("What is 2/3 + 1/6? Answer as a fraction in its simplest form.", "5/6"),
    ("What is 0.4 × 0.5?", "0.20"),
    ("What is 25% of 64?", "16"),
    ("What is 7.2 ÷ 0.8?", "9"),
    ("What is 1/2 + 1/4? Answer as a fraction.", "3/4"),
    ("A shirt costs $80 and is 25% off. What does it cost after the discount, in dollars?", "60"),
    ("Write 3/8 as a decimal.", "0.375"),
    ("45 is what percentage of 180?", "25"),
    ("What is 5.6 − 2.85?", "2.75"),
    ("What is 3/4 × 2/9? Answer as a fraction in its simplest form.", "1/6"),
    ("What is 0.125 × 8?", "1"),
    ("What is the mean of 4, 7, 10 and 15?", "9"),
    ("Write 2 1/2 as a decimal.", "2.50"),
    ("What is 120 ÷ 0.4?", "300"),
    ("What is 3/4 of 2/3? Answer as a fraction in its simplest form.", "1/2"),
    ("What is 15% of 60 plus 20% of 45?", "18"),
    ("What is 0.6 + 0.07 + 0.008?", "0.678"),
    ("Write 7/20 as a decimal.", "0.35"),
    ("A price rises from $50 to $60. By what percentage did it rise?", "20"),
    ("What is 5/6 − 1/4? Answer as a fraction in its simplest form.", "7/12"),
    ("What is 1.5 × 1.5?", "2.25"),
    ("Write 11/4 as a decimal.", "2.75"),
    ("What is 40% of 0.5?", "0.20"),
    ("A tank is 3/5 full, which is 36 litres. How many litres does it hold when full?", "60"),
    ("What is 2/5 ÷ 4/15? Answer as a fraction in its simplest form.", "3/2"),
    ("Write 0.75 as a percentage.", "75"),
]

def questions(static_dir) -> dict[str, list[dict]]:
    """section -> its questions in order, as Question field values."""
    def side(n):
        return "ABC"[(n - 1) % 3]

    return {
        "visual": [{"text": NEXT_SHAPE if (static_dir / f"images/visual/{n}.jpg").is_file() else ODD_ONE_OUT,
                    "answer": answer} for n, answer in sorted(VISUAL.items())],
        "numerical": [{"text": "Which number replaces the question mark (?)?", "answer": str(answer)}
                      for answer in NUMERICAL],
        "verbal": [{"text": text, "answer": answer, **choice_fields(choices)} for text, answer, choices in VERBAL],
        "sequential": [{"text": "Which shapes replace 1 and 2? Type their letters.",
                        "answer": answer} for answer in SEQUENTIAL],
        "spatial": [{"text": "Is each figure the top one, only turned or flipped?", "answer": answer} for answer in SPATIAL],
        "3d": [{"text": f"Side {side(n)}: which view is correct? Type i, ii, iii, iv or v.",
                "answer": answer} for n, answer in enumerate(THREE_D, 1)],
        "system": [{"text": "Type the letter for each symbol.", "answer": answer}
                   for answer in SYSTEM],
        "vocabulary": [{"text": word, "answer": answer, **choice_fields(choices)}
                       for word, answer, choices in VOCABULARY],
        "figurework": [{"text": text, "answer": answer} for text, answer in FIGUREWORK],
    }


def choice_fields(choices: list[str]) -> dict[str, str]:
    return {f"choice_{i}": choice for i, choice in enumerate(choices, 1)}
