
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NX-Provision — Clusters</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#0b1220; color:#e9eef7; }
    .navbar, .offcanvas, .modal-content { background:#101a2b; }
    .card { background:#0f1a2a; border:1px solid #1b2b4b; }
    .form-control, .form-select { background:#0b1220; color:#e9eef7; border-color:#243653; }
    .form-control:focus, .form-select:focus { background:#0b1220; color:#e9eef7; border-color:#4b7bd4; box-shadow:none; }
    .progress { background:#0b1220; height:8px; }
    .badge { font-weight:500; }
    .badge-up { background:#1e7e34; }
    .badge-maint { background:#b8860b; }
    .badge-down { background:#8b0000; }
    .pill-yes { background:#1e7e34; }
    .pill-no { background:#8b0000; }
    a { color:#7fb3ff; }
    a:hover { color:#a7c9ff; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark border-bottom border-primary-subtle">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">NX-Provision</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#filters" aria-controls="filters">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="d-flex">
      <?php if ($isAdmin): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">+ Add Cluster</button>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container-fluid py-3">
  <?php foreach ($flash as $f): ?>
    <div class="alert alert-<?=htmlspecialchars($f['type'])?>"><?=htmlspecialchars($f['msg'])?></div>
  <?php endforeach; ?>

  <div class="row mb-3">
    <div class="col-md-3">
      <form class="d-flex gap-2" method="get">
        <input class="form-control" type="search" name="q" value="<?=htmlspecialchars($search)?>" placeholder="Search name/site/provider/tags">
        <button class="btn btn-outline-light" type="submit">Search</button>
      </form>
    </div>
    <div class="col-md-3">
      <form method="get" class="d-flex gap-2">
        <select class="form-select" name="status">
          <option value="">All status</option>
          <option value="up" <?= $statusFilter==='up'?'selected':'' ?>>Up</option>
          <option value="maintenance" <?= $statusFilter==='maintenance'?'selected':'' ?>>Maintenance</option>
          <option value="down" <?= $statusFilter==='down'?'selected':'' ?>>Down</option>
        </select>
        <select class="form-select" name="site">
          <option value="">All sites</option>
          <?php foreach ($sites as $s): ?>
            <option value="<?=htmlspecialchars($s)?>" <?= $siteFilter===$s?'selected':'' ?>><?=htmlspecialchars($s)?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-outline-light">Apply</button>
      </form>
    </div>
  </div>

  <div class="row g-3">
    <?php if (empty($clusters)): ?>
      <div class="col-12"><div class="card p-4">No clusters found.</div></div>
    <?php endif; ?>

    <?php foreach ($clusters as $c):
      $cpuP = percent($c['used_cpu'], $c['total_cpu']);
      $ramP = percent($c['used_ram_gb'], $c['total_ram_gb']);
      $stoP = percent($c['used_storage_gb'], $c['total_storage_gb']);
      $can = can_create_vm($c);
    ?>
      <div class="col-xl-4 col-lg-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h5 class="card-title mb-1"><?=htmlspecialchars($c['name'])?></h5>
                <div class="small text-secondary">Provider: <?=htmlspecialchars($c['provider'])?> • Site: <?=htmlspecialchars($c['site'])?></div>
              </div>
              <div>
                <?php
                  $badgeClass = $c['status']==='up'?'badge-up':($c['status']==='maintenance'?'badge-maint':'badge-down');
                ?>
                <span class="badge <?=$badgeClass?> text-white text-uppercase"><?=htmlspecialchars($c['status'])?></span>
              </div>
            </div>
            <?php if (!empty($c['mgmt_vip'])): ?>
              <div class="mb-2 small">Mgmt: <a href="<?=htmlspecialchars($c['mgmt_vip'])?>" target="_blank" rel="noopener"><?=htmlspecialchars($c['mgmt_vip'])?></a></div>
            <?php endif; ?>
            <?php if (!empty($c['api_endpoint'])): ?>
              <div class="mb-3 small">API: <code><?=htmlspecialchars($c['api_endpoint'])?></code></div>
            <?php endif; ?>

            <div class="mb-2">CPU (<?=$c['used_cpu']?>/<?=$c['total_cpu']?>)
              <div class="progress"><div class="progress-bar" role="progressbar" style="width: <?=$cpuP?>%"></div></div>
            </div>
            <div class="mb-2">RAM (<?=$c['used_ram_gb']?>/<?=$c['total_ram_gb']?> GB)
              <div class="progress"><div class="progress-bar" role="progressbar" style="width: <?=$ramP?>%"></div></div>
            </div>
            <div class="mb-3">Storage (<?=$c['used_storage_gb']?>/<?=$c['total_storage_gb']?> GB)
              <div class="progress"><div class="progress-bar" role="progressbar" style="width: <?=$stoP?>%"></div></div>
            </div>

            <div class="d-flex gap-2 align-items-center">
              <span class="badge <?=$can?'pill-yes':'pill-no'?> text-white">New VM: <?=$can?'Yes':'No'?></span>
              <span class="small text-secondary">(min: <?=$c['min_cpu_per_vm']?> vCPU • <?=$c['min_ram_gb_per_vm']?> GB • <?=$c['min_storage_gb_per_vm']?> GB)</span>
            </div>

            <?php if (!empty($c['tags'])): ?>
              <div class="mt-2 small">Tags: <?=htmlspecialchars($c['tags'])?></div>
            <?php endif; ?>
            <?php if (!empty($c['notes'])): ?>
              <div class="mt-1 small text-secondary">Note: <?=nl2br(htmlspecialchars($c['notes']))?></div>
            <?php endif; ?>

          </div>
          <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="small text-secondary">Last seen: <?=htmlspecialchars($c['last_seen'] ?: '-')?></div>
            <?php if ($isAdmin): ?>
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalEdit"
                        data-cluster='<?=json_encode($c, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)?>'>Edit</button>
                <form method="post" onsubmit="return confirm('Delete this cluster?');">
                  <input type="hidden" name="csrf" value="<?=$CSRF?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?=$c['id']?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="modalCreate" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="csrf" value="<?=$CSRF?>">
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title">Add Cluster</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php include __DIR__.'/_form_cluster_fields.php'; /* If you don't have partial, the inline fallback below will be used. */ ?>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="post" id="editForm">
        <input type="hidden" name="csrf" value="<?=$CSRF?>">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="edit_id" value="">
        <div class="modal-header">
          <h5 class="modal-title">Edit Cluster</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="editFields">
          <!-- fields injected by JS using the same layout as create -->
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Inline field builder (fallback if no partial file present)
function clusterFields(prefix, data) {
  data = data || {};
  const val = k => (data[k] ?? '');
  return `
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Name</label>
      <input class="form-control" name="name" value="${val('name')}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Provider</label>
      <input class="form-control" name="provider" value="${val('provider') || 'On-Prem'}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Site</label>
      <input class="form-control" name="site" value="${val('site') || 'DC-1'}">
    </div>

    <div class="col-md-6">
      <label class="form-label">Mgmt VIP (URL)</label>
      <input class="form-control" name="mgmt_vip" value="${val('mgmt_vip')}">
    </div>
    <div class="col-md-6">
      <label class="form-label">API Endpoint</label>
      <input class="form-control" name="api_endpoint" value="${val('api_endpoint')}">
    </div>

    <div class="col-md-4">
      <label class="form-label">Status</label>
      <select class="form-select" name="status">
        ${['up','maintenance','down'].map(s => `<option value="${s}" ${val('status')===s?'selected':''}>${s}</option>`).join('')}
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Tags</label>
      <input class="form-control" name="tags" value="${val('tags')}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Notes</label>
      <input class="form-control" name="notes" value="${val('notes')}">
    </div>

    <div class="col-md-4">
      <label class="form-label">Total vCPU</label>
      <input type="number" class="form-control" name="total_cpu" value="${val('total_cpu')||0}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Used vCPU</label>
      <input type="number" class="form-control" name="used_cpu" value="${val('used_cpu')||0}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Min vCPU / VM</label>
      <input type="number" class="form-control" name="min_cpu_per_vm" value="${val('min_cpu_per_vm')||2}">
    </div>

    <div class="col-md-4">
      <label class="form-label">Total RAM (GB)</label>
      <input type="number" class="form-control" name="total_ram_gb" value="${val('total_ram_gb')||0}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Used RAM (GB)</label>
      <input type="number" class="form-control" name="used_ram_gb" value="${val('used_ram_gb')||0}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Min RAM / VM (GB)</label>
      <input type="number" class="form-control" name="min_ram_gb_per_vm" value="${val('min_ram_gb_per_vm')||4}">
    </div>

    <div class="col-md-4">
      <label class="form-label">Total Storage (GB)</label>
      <input type="number" class="form-control" name="total_storage_gb" value="${val('total_storage_gb')||0}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Used Storage (GB)</label>
      <input type="number" class="form-control" name="used_storage_gb" value="${val('used_storage_gb')||0}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Min Storage / VM (GB)</label>
      <input type="number" class="form-control" name="min_storage_gb_per_vm" value="${val('min_storage_gb_per_vm')||50}">
    </div>
  </div>`;
}

// Inject create modal fields if partial missing
(function ensureCreateFields(){
  const container = document.querySelector('#modalCreate .modal-body');
  if (container && container.children.length === 0) {
    container.innerHTML = clusterFields('create', {});
  }
})();

// Edit modal populate
const modalEdit = document.getElementById('modalEdit');
modalEdit?.addEventListener('show.bs.modal', (ev) => {
  const btn = ev.relatedTarget;
  if (!btn) return;
  const raw = btn.getAttribute('data-cluster');
  let data = {};
  try { data = JSON.parse(raw); } catch {}
  document.getElementById('edit_id').value = data.id || '';
  document.getElementById('editFields').innerHTML = clusterFields('edit', data);
});
</script>
</body>
</html>
