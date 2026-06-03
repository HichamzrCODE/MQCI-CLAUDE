<?php

class Article {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    private function resolvePr(array $data): float {
        return (float)($data['pr'] ?? 0);
    }

    public function getTotalCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM articles WHERE deleted_at IS NULL");
        return (int)$stmt->fetchColumn();
    }

    public function getLimited(int $limit = 50): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, f.nom_fournisseurs
             FROM articles a
             INNER JOIN fournisseurs f ON a.fournisseur_id = f.id_fournisseurs
             WHERE a.deleted_at IS NULL
             ORDER BY a.nom_art ASC LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchFull(string $term, int $limit = 50): array {
        $like = '%' . $term . '%';
        $stmt = $this->db->prepare(
            "SELECT a.*, f.nom_fournisseurs
             FROM articles a
             INNER JOIN fournisseurs f ON a.fournisseur_id = f.id_fournisseurs
             WHERE a.deleted_at IS NULL
               AND (a.nom_art LIKE ? OR a.sku LIKE ? OR f.nom_fournisseurs LIKE ?)
             ORDER BY a.nom_art ASC LIMIT ?"
        );
        $stmt->bindValue(1, $like, PDO::PARAM_STR);
        $stmt->bindValue(2, $like, PDO::PARAM_STR);
        $stmt->bindValue(3, $like, PDO::PARAM_STR);
        $stmt->bindValue(4, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT a.*, f.nom_fournisseurs
             FROM articles a
             INNER JOIN fournisseurs f ON a.fournisseur_id = f.id_fournisseurs
             WHERE a.deleted_at IS NULL
             ORDER BY a.nom_art ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT a.*,
                    f.nom_fournisseurs,
                    fa.nom_fournisseurs AS nom_fournisseur_alt,
                    c.nom AS nom_categorie,
                    u.username AS created_by_name,
                    uu.username AS updated_by_name
             FROM articles a
             INNER JOIN fournisseurs f ON a.fournisseur_id = f.id_fournisseurs
             LEFT JOIN fournisseurs fa ON a.fournisseur_alternatif_id = fa.id_fournisseurs
             LEFT JOIN categories c ON a.categorie_id = c.id
             LEFT JOIN users u ON a.created_by = u.id_users
             LEFT JOIN users uu ON a.updated_by = uu.id_users
             WHERE a.id_articles = ? AND a.deleted_at IS NULL"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ✅ CORRIGÉ : INSERT ET EXECUTE SYNCHRONISÉS
    public function create(array $data, int $userId): int {
    $stmt = $this->db->prepare(
        "INSERT INTO articles
            (nom_art, sku, pr, prix_vente,
             prix_detail, prix_semi_gros, prix_gros,
             fournisseur_id, fournisseur_alternatif_id,
             poids_kg, longueur_cm, largeur_cm, hauteur_cm, couleur,
             unite_mesure, stock_minimal, stock_maximal, quantite_totale,
             categorie_id, statut, notes_internes, created_by)
         VALUES
            (:nom_art, :sku, :pr, :prix_vente,
             :prix_detail, :prix_semi_gros, :prix_gros,
             :fournisseur_id, :fournisseur_alternatif_id,
             :poids_kg, :longueur_cm, :largeur_cm, :hauteur_cm, :couleur,
             :unite_mesure, :stock_minimal, :stock_maximal, :quantite_totale,
             :categorie_id, :statut, :notes_internes, :created_by)"
    );

    $pr = $this->resolvePr($data);

    // Prix: on autorise NULL, mais on peut fallback prix_detail <- prix_vente si tu veux
    $prixVente = (float)($data['prix_vente'] ?? 0);

    $prixDetail    = ($data['prix_detail'] ?? '') !== '' ? (float)$data['prix_detail'] : null;
    $prixSemiGros  = ($data['prix_semi_gros'] ?? '') !== '' ? (float)$data['prix_semi_gros'] : null;
    $prixGros      = ($data['prix_gros'] ?? '') !== '' ? (float)$data['prix_gros'] : null;

    if ($prixDetail === null && $prixVente > 0) {
        $prixDetail = $prixVente; // fallback pratique
    }

    $stmt->execute([
        ':nom_art'                   => $data['nom_art'],
        ':sku'                       => ($data['sku'] ?? '') !== '' ? $data['sku'] : null,
        ':pr'                        => $pr,
        ':prix_vente'                => $prixVente,

        ':prix_detail'               => $prixDetail,
        ':prix_semi_gros'            => $prixSemiGros,
        ':prix_gros'                 => $prixGros,

        ':fournisseur_id'            => (int)$data['fournisseur_id'],
        ':fournisseur_alternatif_id' => ($data['fournisseur_alternatif_id'] ?? '') !== '' ? (int)$data['fournisseur_alternatif_id'] : null,
        ':poids_kg'                  => ($data['poids_kg'] ?? '') !== '' ? (float)$data['poids_kg'] : null,
        ':longueur_cm'               => ($data['longueur_cm'] ?? '') !== '' ? (float)$data['longueur_cm'] : null,
        ':largeur_cm'                => ($data['largeur_cm'] ?? '') !== '' ? (float)$data['largeur_cm'] : null,
        ':hauteur_cm'                => ($data['hauteur_cm'] ?? '') !== '' ? (float)$data['hauteur_cm'] : null,
        ':couleur'                   => ($data['couleur'] ?? '') !== '' ? $data['couleur'] : null,
        ':unite_mesure'              => ($data['unite_mesure'] ?? '') !== '' ? $data['unite_mesure'] : 'Piece',
        ':stock_minimal'             => (int)($data['stock_minimal'] ?? 0),
        ':stock_maximal'             => (int)($data['stock_maximal'] ?? 0),
        ':quantite_totale'           => (int)($data['quantite_totale'] ?? 0),
        ':categorie_id'              => ($data['categorie_id'] ?? '') !== '' ? (int)$data['categorie_id'] : null,
        ':statut'                    => $data['statut'] ?? 'actif',
        ':notes_internes'            => ($data['notes_internes'] ?? '') !== '' ? $data['notes_internes'] : null,
        ':created_by'                => $userId,
    ]);

    return (int)$this->db->lastInsertId();
}

    public function update(int $id, array $data, int $userId): void {
    $pr = $this->resolvePr($data);

    $prixVente = (float)($data['prix_vente'] ?? 0);

    $prixDetail    = ($data['prix_detail'] ?? '') !== '' ? (float)$data['prix_detail'] : null;
    $prixSemiGros  = ($data['prix_semi_gros'] ?? '') !== '' ? (float)$data['prix_semi_gros'] : null;
    $prixGros      = ($data['prix_gros'] ?? '') !== '' ? (float)$data['prix_gros'] : null;

    if ($prixDetail === null && $prixVente > 0) {
        $prixDetail = $prixVente;
    }

    $stmt = $this->db->prepare(
        "UPDATE articles SET
            nom_art = :nom_art,
            sku = :sku,
            pr = :pr,
            prix_vente = :prix_vente,
            prix_detail = :prix_detail,
            prix_semi_gros = :prix_semi_gros,
            prix_gros = :prix_gros,
            fournisseur_id = :fournisseur_id,
            fournisseur_alternatif_id = :fournisseur_alternatif_id,
            poids_kg = :poids_kg,
            longueur_cm = :longueur_cm,
            largeur_cm = :largeur_cm,
            hauteur_cm = :hauteur_cm,
            couleur = :couleur,
            unite_mesure = :unite_mesure,
            stock_minimal = :stock_minimal,
            stock_maximal = :stock_maximal,
            quantite_totale = :quantite_totale,
            categorie_id = :categorie_id,
            statut = :statut,
            notes_internes = :notes_internes,
            updated_by = :updated_by
         WHERE id_articles = :id AND deleted_at IS NULL"
    );

    $stmt->execute([
        ':nom_art'                   => $data['nom_art'],
        ':sku'                       => ($data['sku'] ?? '') !== '' ? $data['sku'] : null,
        ':pr'                        => $pr,
        ':prix_vente'                => $prixVente,
        ':prix_detail'               => $prixDetail,
        ':prix_semi_gros'            => $prixSemiGros,
        ':prix_gros'                 => $prixGros,
        ':fournisseur_id'            => (int)$data['fournisseur_id'],
        ':fournisseur_alternatif_id' => ($data['fournisseur_alternatif_id'] ?? '') !== '' ? (int)$data['fournisseur_alternatif_id'] : null,
        ':poids_kg'                  => ($data['poids_kg'] ?? '') !== '' ? (float)$data['poids_kg'] : null,
        ':longueur_cm'               => ($data['longueur_cm'] ?? '') !== '' ? (float)$data['longueur_cm'] : null,
        ':largeur_cm'                => ($data['largeur_cm'] ?? '') !== '' ? (float)$data['largeur_cm'] : null,
        ':hauteur_cm'                => ($data['hauteur_cm'] ?? '') !== '' ? (float)$data['hauteur_cm'] : null,
        ':couleur'                   => ($data['couleur'] ?? '') !== '' ? $data['couleur'] : null,
        ':unite_mesure'              => ($data['unite_mesure'] ?? '') !== '' ? $data['unite_mesure'] : 'Piece',
        ':stock_minimal'             => (int)($data['stock_minimal'] ?? 0),
        ':stock_maximal'             => (int)($data['stock_maximal'] ?? 0),
        ':quantite_totale'           => (int)($data['quantite_totale'] ?? 0),
        ':categorie_id'              => ($data['categorie_id'] ?? '') !== '' ? (int)$data['categorie_id'] : null,
        ':statut'                    => $data['statut'] ?? 'actif',
        ':notes_internes'            => ($data['notes_internes'] ?? '') !== '' ? $data['notes_internes'] : null,
        ':updated_by'                => $userId,
        ':id'                        => $id,
    ]);
}

    public function softDelete(int $id, int $userId): void {
        $stmt = $this->db->prepare(
            "UPDATE articles SET deleted_at = NOW(), updated_by = ? WHERE id_articles = ?"
        );
        $stmt->execute([$userId, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM articles WHERE id_articles = ?");
        $stmt->execute([$id]);
    }

    public function updateImage(int $id, string $imagePath, int $userId): void {
        $stmt = $this->db->prepare(
            "UPDATE articles SET image_path = ?, updated_by = ? WHERE id_articles = ?"
        );
        $stmt->execute([$imagePath, $userId, $id]);
    }

    public function searchByName(string $term): array {
        $like = '%' . $term . '%';
        $stmt = $this->db->prepare(
            "SELECT a.id_articles, a.nom_art, a.pr, f.nom_fournisseurs
             FROM articles a
             INNER JOIN fournisseurs f ON a.fournisseur_id = f.id_fournisseurs
             WHERE a.deleted_at IS NULL AND a.nom_art LIKE ?
             ORDER BY a.nom_art ASC"
        );
        $stmt->execute([$like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllForExport(): array {
        $stmt = $this->db->query(
            "SELECT a.id_articles, a.sku, a.nom_art, a.pr,
                    a.prix_vente, a.quantite_totale, a.statut,
                    a.unite_mesure, a.stock_minimal, a.stock_maximal,
                    a.poids_kg, a.couleur, a.notes_internes,
                    f.nom_fournisseurs
             FROM articles a
             INNER JOIN fournisseurs f ON a.fournisseur_id = f.id_fournisseurs
             WHERE a.deleted_at IS NULL
             ORDER BY a.nom_art ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCategories(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM categories ORDER BY nom ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

        // ----------------------------------------------------------------
    // Historique prix
    // ----------------------------------------------------------------

    public function addPrixHistorique(
        int    $articleId,
        ?float $prAncien,
        ?float $prNouveau,
        ?float $pvAncien,
        ?float $pvNouveau,
        int    $userId,
        string $raison = ''
    ): void {
        $stmt = $this->db->prepare(
            "INSERT INTO articles_prix_historique
                (article_id, prix_revient_ancien, prix_revient_nouveau,
                 prix_vente_ancien, prix_vente_nouveau, changed_by, raison)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $articleId,
            $prAncien,
            $prNouveau,
            $pvAncien,
            $pvNouveau,
            $userId,
            $raison ?: null
        ]);
    }

    public function getPrixHistorique(int $articleId): array {
        $stmt = $this->db->prepare(
            "SELECT h.*, u.username AS changed_by_name
             FROM articles_prix_historique h
             LEFT JOIN users u ON h.changed_by = u.id_users
             WHERE h.article_id = ?
             ORDER BY h.changed_at DESC
             LIMIT 50"
        );
        $stmt->execute([$articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}