<?php
/**
 * AI Connector - Bridge between PHP and the Python Decision Tree model.
 * Usage: $result = AIConnector::predict($data);
 */
class AIConnector {

    private static $pythonPath = 'python';
    private static $scriptPath = '';

    public static function init() {
        // Path to predict.py relative to project root
        self::$scriptPath = __DIR__ . '/../ai_engine/predict.py';
    }

    /**
     * Predict student performance class (L / M / H).
     *
     * @param array $data Associative array with student features:
     *   - gender (M/F)
     *   - NationalITy (string)
     *   - PlaceofBirth (string)
     *   - StageID (lowerlevel/MiddleSchool/HighSchool)
     *   - GradeID (G-02 .. G-12)
     *   - SectionID (A/B/C)
     *   - Topic (IT/Math/Arabic/...)
     *   - Semester (F/S)
     *   - Relation (Father/Mum)
     *   - raisedhands (0-100)
     *   - VisITedResources (0-100)
     *   - AnnouncementsView (0-100)
     *   - Discussion (0-100)
     *   - ParentAnsweringSurvey (Yes/No)
     *   - ParentschoolSatisfaction (Good/Bad)
     *   - StudentAbsenceDays (Under-7/Above-7)
     *
     * @return array ['prediction' => 'H|M|L', 'confidence' => float, 'status' => 'success|failed']
     */
    public static function predict(array $data): array {
        self::init();

        if (!file_exists(self::$scriptPath)) {
            return ['error' => 'predict.py not found at ' . self::$scriptPath, 'status' => 'failed'];
        }

        // Write JSON to a temp file to avoid Windows shell-escaping issues
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phenotyping_input_' . uniqid() . '.json';
        file_put_contents($tmpFile, json_encode($data));

        $script  = escapeshellarg(self::$scriptPath);
        $tmp     = escapeshellarg($tmpFile);
        $command = self::$pythonPath . " {$script} {$tmp} 2>&1";

        $output = shell_exec($command);

        // Clean up temp file
        @unlink($tmpFile);

        if ($output === null) {
            return ['error' => 'shell_exec returned null. Check PHP config (disable_functions).', 'status' => 'failed'];
        }

        $result = json_decode(trim($output), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON from Python: ' . $output, 'status' => 'failed'];
        }

        return $result;
    }

    /**
     * Map prediction code to Arabic label for UI.
     */
    public static function getArabicLabel(string $code): string {
        return match($code) {
            'H' => 'متفوق 🌟',
            'M' => 'متوسط 📘',
            'L' => 'يحتاج متابعة ⚠️',
            default => 'غير محدد'
        };
    }

    /**
     * Map prediction to Bootstrap badge class.
     */
    public static function getBadgeClass(string $code): string {
        return match($code) {
            'H' => 'success',
            'M' => 'warning',
            'L' => 'danger',
            default => 'secondary'
        };
    }
}
?>
