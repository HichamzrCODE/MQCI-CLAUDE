<?php
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/Tresorerie.php';

class TresorerieController {
    private Tresorerie $model;

    public function __construct(PDO $db) {
        $this->model = new Tresorerie($db);
    }

    public function index(): array {
        $type = $_GET['type'] ?? ''; // caisses | banques | mobile
        $items = [];

        if (in_array($type, ['caisses','banques','mobile'], true)) {
            $items = $this->model->list($type);
        }

        return [
            'view' => 'tresorerie/index',
            'data' => [
                'type' => $type,
                'items' => $items,
                'csrf_field' => CsrfMiddleware::field(),
            ]
        ];
    }

    public function create(array $post): array {
        CsrfMiddleware::verify();

        $type = $_GET['type'] ?? '';
        if (!in_array($type, ['caisses','banques','mobile'], true)) {
            return ['view' => 'error', 'data' => ['message' => 'Type trésorerie invalide']];
        }

        // validations minimales
        if ($type === 'caisses') {
            if (trim($post['nom'] ?? '') === '') {
                return $this->redirectWithError($type, "Le nom de la caisse est obligatoire.");
            }
        }
        if ($type === 'banques') {
            if (trim($post['nom'] ?? '') === '') {
                return $this->redirectWithError($type, "Le nom de la banque est obligatoire.");
            }
        }
        if ($type === 'mobile') {
            if (trim($post['nom_compte'] ?? '') === '' || trim($post['operateur'] ?? '') === '' || trim($post['telephone'] ?? '') === '') {
                return $this->redirectWithError($type, "Nom compte, opérateur et téléphone sont obligatoires.");
            }
        }

        $this->model->create($type, $post);

        header('Location: index.php?action=tresorerie&type=' . urlencode($type));
        exit();
    }

    public function delete(array $query): void {
        CsrfMiddleware::verify();

        $type = $query['type'] ?? '';
        $id = $query['id'] ?? null;

        if (!in_array($type, ['caisses','banques','mobile'], true)) die("Type invalide.");
        if ($id === null || !is_numeric($id)) die("ID invalide.");

        $this->model->delete($type, (int)$id);

        header('Location: index.php?action=tresorerie&type=' . urlencode($type));
        exit();
    }

    private function redirectWithError(string $type, string $message): array {
        $items = $this->model->list($type);
        return [
            'view' => 'tresorerie/index',
            'data' => [
                'type' => $type,
                'items' => $items,
                'error' => $message,
                'csrf_field' => CsrfMiddleware::field(),
            ]
        ];
    }
}