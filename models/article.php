<?php

class Article {
<<<<<<< HEAD
    private $db;
=======
    private PDO $db;
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1

    public function __construct(PDO $db) {
        $this->db = $db;
    }

<<<<<<< HEAD
    public function getTotalCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM articles");
        return $stmt->fetchColumn();
    }

    public function getLimited($limit = 50): array {
        $stmt = $this->db->prepare(
            "SELECT articles.*, fournisseurs.nom_fournisseurs
             FROM articles
             INNER JOIN fournisseurs ON articles.fournisseur_id = fournisseurs.id_fournisseurs
             ORDER BY articles.nom_art ASC LIMIT ?"
        );
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
=======
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
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

<<<<<<< HEAD
    public function searchFull(string $term, $limit = 50): array {
        $searchTerm = '%' . $term . '%';
        $stmt = $this->db->prepare(
            "SELECT articles.*, fournisseurs.nom_fournisseurs
             FROM articles
             INNER JOIN fournisseurs ON articles.fournisseur_id = fournisseurs.id_fournisseurs
             WHERE articles.nom_art LIKE ? OR fournisseurs.nom_fournisseurs LIKE ?
             ORDER BY articles.nom_art ASC LIMIT ?"
        );
        $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(3, (int)$limit, PDO::PARAM_INT);
=======
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
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

<<<<<<< HEAD
    // Jointure fournisseurs
    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT articles.*, fournisseurs.nom_fournisseurs
             FROM articles
             INNER JOIN fournisseurs ON articles.fournisseur_id = fournisseurs.id_fournisseurs
             ORDER BY articles.nom_art ASC"
=======
    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT a.*, f.nom_fournisseurs
             FROM articles a
             INNER JOIN fournisseurs f ON a.fournisseur_id = f.id_fournisseurs
             WHERE a.deleted_at IS NULL
             ORDER BY a.nom_art ASC"
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

<<<<<<< HEAD
    public function create(string $nom_art, float $pr, string $fournisseur_id, int $userId): int {
        $stmt = $this->db->prepare("INSERT INTO articles (nom_art, pr, fournisseur_id, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom_art, $pr, $fournisseur_id, $userId]);
        return (int)$this->db->lastInsertId();
    }

    // Jointure fournisseurs aussi pour l’affichage détaillé
    public function findById(int $id_articles): ?array {
        $stmt = $this->db->prepare(
            "SELECT articles.*, fournisseurs.nom_fournisseurs
             FROM articles
             INNER JOIN fournisseurs ON articles.fournisseur_id = fournisseurs.id_fournisseurs
             WHERE articles.id_articles = ?"
        );
        $stmt->execute([$id_articles]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        return $article ?: null;
    }

    public function update(int $id, string $nom_art, float $pr, string $fournisseur_id): void {
        $stmt = $this->db->prepare("UPDATE articles SET nom_art = ?, pr = ?, fournisseur_id = ? WHERE id_articles = ? ");
        $stmt->execute([$nom_art, $pr, $fournisseur_id, $id]);
    }

    public function delete(int $id_articles): void {
        $stmt = $this->db->prepare("DELETE FROM articles WHERE id_articles = ?");
        $stmt->execute([$id_articles]);
    }

    public function searchByName(string $term): array {
    $term = '%' . $term . '%';
    $stmt = $this->db->prepare(
        "SELECT articles.id_articles, articles.nom_art, articles.pr, fournisseurs.nom_fournisseurs
         FROM articles
         INNER JOIN fournisseurs ON articles.fournisseur_id = fournisseurs.id_fournisseurs
         WHERE articles.nom_art LIKE ?
         ORDER BY articles.nom_art ASC"
    );
    $stmt->execute([$term]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
=======
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
                 fournisseur_id, fournisseur_alternatif_id,
                 poids_kg, longueur_cm, largeur_cm, hauteur_cm, couleur,
                 unite_mesure, stock_minimal, stock_maximal, quantite_totale,
                 categorie_id, statut, notes_internes, created_by)
             VALUES
                (:nom_art, :sku, :pr, :prix_vente,
                 :fournisseur_id, :fournisseur_alternatif_id,
                 :poids_kg, :longueur_cm, :largeur_cm, :hauteur_cm, :couleur,
                 :unite_mesure, :stock_minimal, :stock_maximal, :quantite_totale,
                 :categorie_id, :statut, :notes_internes, :created_by)"
        );
        $pr = $this->resolvePr($data);
        $stmt->execute([
            ':nom_art'                   => $data['nom_art'],
            ':sku'                       => ($data['sku'] ?? '') !== '' ? $data['sku'] : null,
            ':pr'                        => $pr,
            ':prix_vente'                => (float)($data['prix_vente'] ?? 0),
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
        $stmt = $this->db->prepare(
            "UPDATE articles SET
                nom_art = :nom_art,
                sku = :sku,
                pr = :pr,
                prix_vente = :prix_vente,
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
            ':prix_vente'                => (float)($data['prix_vente'] ?? 0),
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
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}