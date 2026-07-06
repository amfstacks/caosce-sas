<?php
class UuidHelper {
    /**
     * Generates a universally unique identifier (UUID v4)
     * 
     * @return string
     */
    public static function v4() {
        $data = random_bytes(16);
        
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

// Usage Example:
// $newSchoolId = UuidHelper::v4();
// $sql = "INSERT INTO schools (id, name) VALUES (:id, :name)";
?>