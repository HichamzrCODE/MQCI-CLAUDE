<?php

class Settings {
    private PDO $db;
    private string $uploadDir;

    public function __construct(PDO $db) {
        $this->db = $db;
        // Utiliser un chemin absolu correct
        $this->uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/maqci/public/uploads/app/';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    // Récupérer un paramètre
    public function get(string $key, $default = null) {
        $stmt = $this->db->prepare(
            "SELECT `value` FROM settings WHERE `key` = ?"
        );
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['value'] : $default;
    }

    // Définir un paramètre
    public function set(string $key, $value): void {
        $stmt = $this->db->prepare("SELECT id FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $exists = $stmt->fetch();
        if ($exists) {
            $stmt = $this->db->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?");
            $stmt->execute([$value, $key]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
    }

    // Récupérer tous les paramètres
    public function getAll(): array {
        $stmt = $this->db->query("SELECT `key`, `value` FROM settings");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }

    // Uploader un logo
    public function uploadLogo($file): array {
        // Vérifications
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'error' => 'Aucun fichier sélectionné'];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'Le fichier n\'a pas été uploadé correctement'];
        }

        if ($file['size'] > 2 * 1024 * 1024) { // 2MB
            return ['success' => false, 'error' => 'La taille du fichier dépasse 2MB'];
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Format non accepté. Utilisez JPG, PNG ou WEBP'];
        }

        try {
            // Créer un nom unique
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'logo-app-' . time() . '.' . $extension;
            $filepath = $this->uploadDir . $filename;

            // Redimensionner l'image
            $this->resizeImage($file['tmp_name'], $filepath);

            // Vérifier que le fichier a été créé
            if (!file_exists($filepath)) {
                return ['success' => false, 'error' => 'Erreur lors de la sauvegarde de l\'image'];
            }

            // Supprimer l'ancien logo
            $oldLogo = $this->get('app_logo_file');
            if ($oldLogo) {
                $oldPath = $this->uploadDir . $oldLogo;
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            // Sauvegarder les paramètres
            $this->set('app_logo_file', $filename);
            $this->set('app_logo_url', '/maqci/public/uploads/app/' . $filename);

            return ['success' => true, 'message' => 'Logo uploadé avec succès !'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Erreur : ' . $e->getMessage()];
        }
    }

    // Redimensionner l'image
    private function resizeImage(string $source, string $destination): void {
        // Vérifier que GD est activé
        if (!extension_loaded('gd')) {
            throw new Exception('L\'extension GD n\'est pas activée');
        }

        // Déterminer le type d'image
        $imageInfo = @getimagesize($source);
        if (!$imageInfo) {
            throw new Exception('Impossible de lire l\'image');
        }

        $mime = $imageInfo['mime'];
        $image = null;

        // Charger l'image selon le type
        if ($mime === 'image/jpeg') {
            $image = @imagecreatefromjpeg($source);
        } elseif ($mime === 'image/png') {
            $image = @imagecreatefrompng($source);
        } elseif ($mime === 'image/webp') {
            $image = @imagecreatefromwebp($source);
        } else {
            throw new Exception('Format d\'image non supporté : ' . $mime);
        }

        if (!$image) {
            throw new Exception('Impossible de charger l\'image');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Redimensionner à 256x256
        $targetSize = 256;
        $newWidth = $targetSize;
        $newHeight = $targetSize;

        // Calculer les proportions
        if ($width > $height) {
            $newHeight = (int)($height * ($targetSize / $width));
        } else {
            $newWidth = (int)($width * ($targetSize / $height));
        }

        // Créer l'image redimensionnée
        $resized = imagecreatetruecolor($targetSize, $targetSize);
        
        // Fond blanc
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);

        // Centrer l'image
        $x = (int)(($targetSize - $newWidth) / 2);
        $y = (int)(($targetSize - $newHeight) / 2);

        imagecopyresampled(
            $resized, $image,
            $x, $y, 0, 0,
            $newWidth, $newHeight,
            $width, $height
        );

        // Sauvegarder en PNG
        $result = imagepng($resized, $destination, 9);
        
        imagedestroy($image);
        imagedestroy($resized);

        if (!$result) {
            throw new Exception('Impossible de sauvegarder l\'image');
        }
    }

    // Obtenir l'URL du logo
    public function getLogoUrl(): ?string {
        $logoUrl = $this->get('app_logo_url');
        $logoFile = $this->get('app_logo_file');
        
        if ($logoFile && file_exists($this->uploadDir . $logoFile)) {
            return $logoUrl;
        }
        return null;
    }

    // Obtenir le nom du fichier logo
    public function getLogoFile(): ?string {
        return $this->get('app_logo_file');
    }
}