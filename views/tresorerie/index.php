<?php $pageTitle = "Trésorerie"; include '../views/layout.php'; ?>

<div class="page-header">
  <h1><i class="fas fa-cash-register"></i> Trésorerie</h1>
  <p class="page-description">Gérez Caisses, Banques et Mobile money</p>
</div>

<?php if (!empty($error ?? null)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (($type ?? '') === ''): ?>
  <!-- ETAPE 1 : CHOIX -->
  <div class="card">
    <div class="card-body p-3">
      <div class="mb-2 font-weight-bold">Choisir une rubrique</div>

      <div class="row">
        <div class="col-md-12 mb-2">
          <a class="btn btn-light border w-100 text-left py-2" href="index.php?action=tresorerie&type=caisses">
            <div class="d-flex align-items-center">
              <i class="fas fa-cash-register mr-2 text-success"></i>
              <div class="font-weight-bold" style="margin-left:15px;">Caisses</div>
            </div>
          </a>
        </div>

        <div class="col-md-12 mb-2">
          <a class="btn btn-light border w-100 text-left py-2" href="index.php?action=tresorerie&type=banques">
            <div class="d-flex align-items-center">
              <i class="fas fa-building-columns mr-2 text-primary"></i>
              <div class="font-weight-bold" style="margin-left:15px;">Banques</div>
            </div>
          </a>
        </div>

        <div class="col-md-12 mb-2">
          <a class="btn btn-light border w-100 text-left py-2" href="index.php?action=tresorerie&type=mobile">
            <div class="d-flex align-items-center">
              <i class="fas fa-mobile-screen mr-2" style="color:#7c3aed;"></i>
              <div class="font-weight-bold" style="margin-left:15px;">Mobile money</div>
            </div>
          </a>
        </div>
      </div>

    </div>
  </div>

<?php else: ?>
  <!-- ETAPE 2 : CRUD -->
  <?php
    $title = $type === 'caisses' ? 'Caisses' : ($type === 'banques' ? 'Banques' : 'Mobile money');
  ?>

  <div class="card mb-3">
    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
      <div>
        <div class="font-weight-bold"><?= htmlspecialchars($title) ?></div>
        <div class="text-muted small">Ajouter et gérer la liste</div>
      </div>
      <a class="btn btn-sm btn-outline-secondary" href="index.php?action=tresorerie">Changer</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body p-3">
      <form method="post" action="index.php?action=tresorerie/create&type=<?= urlencode($type) ?>">
        <?= $csrf_field ?>

        <?php if ($type === 'caisses'): ?>
          <div class="form-row">
            <div class="form-group col-md-6 mb-2">
              <label class="font-weight-bold mb-1">Nom caisse</label>
              <input type="text" name="nom" class="form-control form-control-sm" required>
            </div>
            <div class="form-group col-md-6 mb-2">
              <label class="font-weight-bold mb-1">Localisation (optionnel)</label>
              <input type="text" name="localisation" class="form-control form-control-sm">
            </div>
          </div>

        <?php elseif ($type === 'banques'): ?>
          <div class="form-row">
            <div class="form-group col-md-4 mb-2">
              <label class="font-weight-bold mb-1">Nom banque</label>
              <input type="text" name="nom" class="form-control form-control-sm" required>
            </div>
            <div class="form-group col-md-4 mb-2">
              <label class="font-weight-bold mb-1">Localisation (optionnel)</label>
              <input type="text" name="localisation" class="form-control form-control-sm">
            </div>
            <div class="form-group col-md-4 mb-2">
              <label class="font-weight-bold mb-1">RIB (optionnel)</label>
              <input type="text" name="rib" class="form-control form-control-sm">
            </div>
          </div>

        <?php else: ?>
          <div class="form-row">
            <div class="form-group col-md-4 mb-2">
              <label class="font-weight-bold mb-1">Nom compte</label>
              <input type="text" name="nom_compte" class="form-control form-control-sm" required>
            </div>
            <div class="form-group col-md-3 mb-2">
              <label class="font-weight-bold mb-1">Opérateur</label>
              <select name="operateur" class="form-control form-control-sm" required>
                <option value="">— Choisir —</option>
                <option value="orange">Orange Money</option>
                <option value="mtn">MTN MoMo</option>
                <option value="moov">Moov Money</option>
                <option value="wave">Wave</option>
              </select>
            </div>
            <div class="form-group col-md-3 mb-2">
              <label class="font-weight-bold mb-1">Téléphone</label>
              <input type="text" name="telephone" class="form-control form-control-sm" required>
            </div>
            <div class="form-group col-md-2 mb-2">
              <label class="font-weight-bold mb-1">Localisation</label>
              <input type="text" name="localisation" class="form-control form-control-sm">
            </div>
          </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary btn-sm">AJOUTER</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead>
          <tr>
            <?php if ($type === 'caisses'): ?>
              <th>Nom</th><th>Localisation</th><th style="width:120px;"></th>
            <?php elseif ($type === 'banques'): ?>
              <th>Nom</th><th>Localisation</th><th>RIB</th><th style="width:120px;"></th>
            <?php else: ?>
              <th>Nom compte</th><th>Opérateur</th><th>Téléphone</th><th>Localisation</th><th style="width:120px;"></th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach (($items ?? []) as $row): ?>
            <tr>
              <?php if ($type === 'caisses'): ?>
                <td><?= htmlspecialchars($row['nom'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['localisation'] ?? '') ?></td>

              <?php elseif ($type === 'banques'): ?>
                <td><?= htmlspecialchars($row['nom'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['localisation'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['rib'] ?? '') ?></td>

              <?php else: ?>
                <td><?= htmlspecialchars($row['nom_compte'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['operateur'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['telephone'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['localisation'] ?? '') ?></td>
              <?php endif; ?>

              <td class="text-right">
                <a class="btn btn-danger btn-sm"
                   href="index.php?action=tresorerie/delete&type=<?= urlencode($type) ?>&id=<?= (int)$row['id'] ?>">
                  Supprimer
                </a>
              </td>
            </tr>
          <?php endforeach; ?>

          <?php if (empty($items)): ?>
            <tr><td colspan="6" class="text-center text-muted p-3">Aucun élément</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php endif; ?>