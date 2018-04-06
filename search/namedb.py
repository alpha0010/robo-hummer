import os.path
import sqlite3

# Map file names to stable integer IDs.
class NameDB:
    def __init__(self, fileName):
        self.conn = sqlite3.connect(fileName)

        # File backing does not exist; initialize it.
        if os.path.getsize(fileName) == 0:
            self.conn.executescript("""
                CREATE TABLE names (
                    name TEXT
                );

                CREATE TABLE featureIDs (
                    nameID INT
                );

                CREATE UNIQUE INDEX names_name_unq ON names (name);
                CREATE INDEX featureids_nameid_idx ON featureIDs (nameID);
            """)

    # Array access: get the ID for a file name, creating if needed.
    def __getitem__(self, key):
        nameID = self.getIdFromName(key)
        if nameID is not None:
            return nameID

        with self.conn:
            cur = self.conn.cursor()
            cur.execute("INSERT INTO names (name) VALUES (?)", (key,))

        return self.getIdFromName(key)

    # Get the pre-existing ID for a file name.
    def getIdFromName(self, name):
        cur = self.conn.cursor()
        cur.execute("SELECT rowID FROM names WHERE name = ?", (name,))
        for row in cur:
            return row[0]
        return None

    # Get the file name corresponding to an ID.
    def getNameFromID(self, nameID):
        cur = self.conn.cursor()
        cur.execute("SELECT name FROM names WHERE rowID = ?", (nameID,))
        for row in cur:
            return row[0]
        return None

    # Generate feature IDs linked to a name.
    def generateIDs(self, nameID, count):
        featureIDs = []
        with self.conn:
            for x in range(count):
                cur = self.conn.cursor()
                cur.execute(
                    "INSERT INTO featureIDs (nameID) VALUES (?)",
                    (nameID,)
                )
                featureIDs.append(cur.lastrowid)
        return featureIDs

    # List matches per file for the given feature IDs.
    def summarizeHits(self, featureIDs):
        with self.conn:
            cur = self.conn.cursor()

            cur.execute("""
                CREATE TEMPORARY TABLE tmpJoinBuff (
                    featureID INT
                )
            """)

            cur.executemany(
                "INSERT INTO tmpJoinBuff (featureID) VALUES (?)",
                [(int(featureID),) for featureID in featureIDs]
            )

            cur.execute("""
                SELECT name, COUNT(*) ct
                FROM names
                JOIN (
                    SELECT nameID
                    FROM featureIDs
                    JOIN tmpJoinBuff
                        ON featureIDs.rowID = tmpJoinBuff.featureID
                ) t1
                    ON names.rowID = t1.nameID
                GROUP BY names.rowID
                ORDER BY ct DESC
            """)

            summary = list(cur)

            cur.execute("DROP TABLE tmpJoinBuff")

            return [{"name": name, "score": score} for name, score in summary]
