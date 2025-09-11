<?php
// --- ดึงข้อมูล OS Support จาก DB โดยเรียงตาม sort_order ---
$stmt = $pdo->query("SELECT * FROM os_support ORDER BY sort_order ASC, id ASC");
$osRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูล user ปัจจุบัน (ใช้จาก session ที่ nx-mainboard.php โหลดไว้)
$currentUser = isset($currentUser) ? $currentUser : null;
$isAdmin = ($currentUser && isset($currentUser['role']) && $currentUser['role'] === 'Admin');

// Filter OS
$selectedFilter = isset($_GET['os_filter']) ? $_GET['os_filter'] : 'all';
function get_os_type($osName) {
  return (preg_match('/^windows/i', $osName)) ? 'windows' : 'linux';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Provision OS Support</title>
  <link rel="stylesheet" href="os-support.css">
  
  <style>
    .filter-bar {
      display: flex;
      gap: 8px;
      margin-bottom: 18px;
      align-items: center;
    }
    .filter-btn {
      border-radius: 7px;
      padding: 6px 22px;
      font-weight: 500;
      border: none;
      background: #e8eef6;
      color: #314a65;
      font-size: 1.04em;
      box-shadow: 0 2px 8px rgba(60,80,180,0.08);
      transition: background 0.18s, color 0.18s;
      cursor: pointer;
      outline: none;
      text-decoration: none;
      display: inline-block;
    }
    .filter-btn.selected, .filter-btn:hover {
      background: linear-gradient(90deg,#2f5fff 0%,#98c2fc 100%);
      color: #fff;
    }
    @media (max-width: 768px) {
      .filter-bar { margin-bottom: 10px; flex-wrap: wrap; gap: 4px; }
      .filter-btn { padding: 5px 12px; font-size: 1em; }
    }
  </style>
</head>
<body>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0 text-center flex-grow-1">Provision OS Support <span class="badge bg-secondary">Manage</span></h3>
    <?php if ($isAdmin): ?>
    <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addOSModal">
      <i class="fa fa-plus"></i> <span class="d-none d-md-inline">Add OS</span>
    </button>
    <?php endif; ?>
    <button class="btn btn-outline-dark d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#osColumnsCanvas" aria-controls="osColumnsCanvas">
      <i class="fa fa-bars"></i>
    </button>
    <?php if ($isAdmin): ?>
    <button class="btn btn-outline-secondary d-none d-md-inline" type="button" data-bs-toggle="offcanvas" data-bs-target="#osRowsCanvas" aria-controls="osRowsCanvas">
      <i class="fa fa-list-ol"></i> Customize Row Order
    </button>
    <?php endif; ?>
  </div>

  <!-- Filter OS Type Button Bar (ใช้ a tag เพื่อแก้ redirect) -->
  <div class="filter-bar mb-0">
    <span class="fw-bold me-2" style="min-width:60px;">Filter:</span>
    <a href="?tab=ossupport&os_filter=all" class="filter-btn<?= $selectedFilter=='all' ? ' selected' : '' ?>">All OS</a>
    <a href="?tab=ossupport&os_filter=windows" class="filter-btn<?= $selectedFilter=='windows' ? ' selected' : '' ?>">Windows</a>
    <a href="?tab=ossupport&os_filter=linux" class="filter-btn<?= $selectedFilter=='linux' ? ' selected' : '' ?>">Linux</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle sortable-table" id="osTable">
      <thead class="table-light">
        <tr>
          <th data-column="os">OS</th>
          <th data-column="new_provision">New Provision</th>
          <th data-column="deep">Deep</th>
          <th data-column="zabbix_agent">Zabbix Agent</th>
          <th data-column="crowdstrike">CrowdStrike</th>
          <th data-column="template">Template</th>
          <th data-column="other">Other</th>
          <th data-column="eos">EOS</th>
          <th data-column="std_nonstd">STD / NON STD</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="osTableBody">
        <?php foreach ($osRows as $row):
          $osType = get_os_type($row['os']);
          if ($selectedFilter == 'all' || $selectedFilter == $osType):
        ?>
        <tr data-id="<?= $row['id'] ?>">
          <td><?= htmlspecialchars($row['os']) ?></td>
          <td><?= $row['new_provision'] ? '<span class="check">&#10003;</span>' : '<span class="uncheck">&#9744;</span>' ?></td>
          <td><?= $row['deep'] ? '<span class="check">&#10003;</span>' : '<span class="uncheck">&#9744;</span>' ?></td>
          <td><?= $row['zabbix_agent'] ? '<span class="check">&#10003;</span>' : '<span class="uncheck">&#9744;</span>' ?></td>
          <td><?= $row['crowdstrike'] ? '<span class="check">&#10003;</span>' : '<span class="uncheck">&#9744;</span>' ?></td>
          <td><?= $row['template'] ? '<span class="check">&#10003;</span>' : '<span class="uncheck">&#9744;</span>' ?></td>
          <td><?= htmlspecialchars($row['other']) ?></td>
          <td><?= htmlspecialchars($row['eos']) ?></td>
          <td><?= htmlspecialchars($row['std_nonstd']) ?></td>
          <td>
            <?php if ($isAdmin): ?>
            <button class="btn btn-sm btn-warning edit-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#editOSModal"><i class="fa fa-edit"></i></button>
            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $row['id'] ?>"><i class="fa fa-trash"></i></button>
            <?php else: ?>
            <span class="text-muted">-</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endif; endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Offcanvas Hamburger for Column Order -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="osColumnsCanvas" aria-labelledby="osColumnsCanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="osColumnsCanvasLabel"><i class="fa fa-bars me-2"></i>Customize Columns</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <div id="os-column-list" class="list-group"></div>
    <div class="mt-3">
      <button class="btn btn-success w-100" id="saveColumnOrder">Save Order</button>
    </div>
  </div>
</div>

<?php if ($isAdmin): ?>
<!-- Offcanvas for Row Order -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="osRowsCanvas" aria-labelledby="osRowsCanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="osRowsCanvasLabel"><i class="fa fa-list-ol me-2"></i>Customize OS Row Order</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <div id="os-row-list" class="list-group"></div>
    <div class="mt-3">
      <button class="btn btn-success w-100" id="saveRowOrder">Save Row Order</button>
      <button class="btn btn-secondary w-100 mt-2" id="resetRowOrder">Reset to Default</button>
    </div>
    <div class="mt-3">
      <small class="text-muted">Row order is visible to all users.</small>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($isAdmin): ?>
<!-- Add OS Modal (Admin only) -->
<div class="modal fade" id="addOSModal" tabindex="-1" aria-labelledby="addOSModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="os-support-add.php">
      <div class="modal-header">
        <h5 class="modal-title" id="addOSModalLabel"><i class="fa fa-plus"></i> Add OS Support</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label class="form-label">OS Name</label>
          <input type="text" name="os" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">New Provision</label>
          <select name="new_provision" class="form-select">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Deep</label>
          <select name="deep" class="form-select">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Zabbix Agent</label>
          <select name="zabbix_agent" class="form-select">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">CrowdStrike</label>
          <select name="crowdstrike" class="form-select">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Template</label>
          <select name="template" class="form-select">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">EOS</label>
          <input type="text" name="eos" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">STD / NON STD</label>
          <input type="text" name="std_nonstd" class="form-control">
        </div>
        <div class="col-md-12">
          <label class="form-label">Other</label>
          <input type="text" name="other" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Add</button>
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit OS Modal (Admin only, populated by JS) -->
<div class="modal fade" id="editOSModal" tabindex="-1" aria-labelledby="editOSModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="os-support-edit.php">
      <div class="modal-header">
        <h5 class="modal-title" id="editOSModalLabel"><i class="fa fa-edit"></i> Edit OS Support</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3" id="editOSFormBody">
        <!-- Filled by JS -->
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" type="submit">Save</button>
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script>
const osColumns = [
  { key:'os', label:'OS' },
  { key:'new_provision', label:'New Provision' },
  { key:'deep', label:'Deep' },
  { key:'zabbix_agent', label:'Zabbix Agent' },
  { key:'crowdstrike', label:'CrowdStrike' },
  { key:'template', label:'Template' },
  { key:'other', label:'Other' },
  { key:'eos', label:'EOS' },
  { key:'std_nonstd', label:'STD / NON STD' }
];
// COLUMN ORDER
function getColOrder() {
  return JSON.parse(localStorage.getItem('osColOrder') || "[]");
}
function setColOrder(order) {
  localStorage.setItem('osColOrder', JSON.stringify(order));
}
function renderColOrderUI() {
  let order = getColOrder();
  if (!order.length) order = osColumns.map(col=>col.key);
  let html = "";
  order.forEach(key=>{
    let col = osColumns.find(c=>c.key===key);
    if (col) html += `<div class="list-group-item" data-key="${col.key}"><i class="fa fa-grip-vertical me-2"></i>${col.label}</div>`;
  });
  document.getElementById('os-column-list').innerHTML = html;
}
function applyColOrder() {
  let order = getColOrder();
  if (!order.length) order = osColumns.map(col=>col.key);
  // Table header
  const ths = Array.from(document.querySelectorAll('#osTable thead th[data-column]'));
  const actionsTh = document.querySelector('#osTable thead th:last-child');
  order.forEach((key, idx) => {
    let th = ths.find(t=>t.dataset.column===key);
    if (th) th.parentNode.appendChild(th);
  });
  if(actionsTh) actionsTh.parentNode.appendChild(actionsTh);
  // Table body
  Array.from(document.querySelectorAll('#osTable tbody tr')).forEach(tr=>{
    let tds = Array.from(tr.children);
    order.forEach((key, idx)=>{
      let thIdx = osColumns.findIndex(c=>c.key===key);
      let td = tds[thIdx];
      if(td) tr.appendChild(td);
    });
    let last = tds[tds.length-1];
    if(last) tr.appendChild(last);
  });
}

// ROW ORDER (สำหรับ admin เท่านั้น)
<?php if ($isAdmin): ?>
function renderRowOrderUI() {
  // Get all rows
  let trs = Array.from(document.querySelectorAll('#osTableBody tr'));
  let html = "";
  trs.forEach(tr=>{
    let osName = tr.querySelector('td').innerText;
    html += `<div class="list-group-item" data-id="${tr.dataset.id}"><i class="fa fa-grip-vertical me-2"></i>${osName}</div>`;
  });
  document.getElementById('os-row-list').innerHTML = html;
}
function applyRowOrder(newOrder) {
  let tbody = document.getElementById('osTableBody');
  let trs = Array.from(tbody.querySelectorAll('tr'));
  if (!newOrder || !newOrder.length) return;
  newOrder.forEach(id=>{
    let tr = trs.find(row=>row.dataset.id === id);
    if (tr) tbody.appendChild(tr);
  });
}
function saveRowOrderToServer(order) {
  fetch('os-support-sort.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(order)
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'ok') {
      var canvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('osRowsCanvas'));
      canvas.hide();
      setTimeout(function(){ location.reload(); }, 400);
    } else {
      alert('Save failed: ' + (data.status || 'Unknown error'));
    }
  })
  .catch(err => {
    alert('Network/server error: ' + err);
  });
}
document.addEventListener('DOMContentLoaded', function(){
  // Column order
  renderColOrderUI();
  applyColOrder();
  let sortableCol = new Sortable(document.getElementById('os-column-list'), {
    animation: 150
  });
  document.getElementById('saveColumnOrder').onclick = function() {
    let order = Array.from(document.querySelectorAll('#os-column-list .list-group-item')).map(el=>el.dataset.key);
    setColOrder(order);
    applyColOrder();
    var canvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('osColumnsCanvas'));
    canvas.hide();
  };

  // Row order
  renderRowOrderUI();
  let sortableRow = new Sortable(document.getElementById('os-row-list'), {
    animation: 150
  });
  document.getElementById('saveRowOrder').onclick = function() {
    let order = Array.from(document.querySelectorAll('#os-row-list .list-group-item')).map(el=>el.dataset.id);
    saveRowOrderToServer(order);
  };
  document.getElementById('resetRowOrder').onclick = function() {
    let ids = Array.from(document.querySelectorAll('#os-row-list .list-group-item')).map(el=>el.dataset.id);
    fetch('os-support-sort.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(ids)
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'ok') {
        var canvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('osRowsCanvas'));
        canvas.hide();
        setTimeout(function(){ location.reload(); }, 400);
      }
    });
  };

  // Edit: Fill Modal From Table Row
  document.querySelectorAll('.edit-btn').forEach(btn=>{
    btn.addEventListener('click', function(){
      var row = document.querySelector('tr[data-id="'+this.dataset.id+'"]');
      var tds = row.querySelectorAll('td');
      let fields = {
        os: tds[0].innerText,
        new_provision: tds[1].querySelector('.check')?1:0,
        deep: tds[2].querySelector('.check')?1:0,
        zabbix_agent: tds[3].querySelector('.check')?1:0,
        crowdstrike: tds[4].querySelector('.check')?1:0,
        template: tds[5].querySelector('.check')?1:0,
        other: tds[6].innerText,
        eos: tds[7].innerText,
        std_nonstd: tds[8].innerText,
        id: row.dataset.id
      };
      let html = `<input type="hidden" name="id" value="${fields.id}">`;
      html += `<div class="col-md-6"><label class="form-label">OS Name</label>
        <input type="text" name="os" class="form-control" value="${fields.os}" required></div>`;
      html += `<div class="col-md-3"><label class="form-label">New Provision</label>
        <select name="new_provision" class="form-select">
          <option value="1"${fields.new_provision==1?' selected':''}>Yes</option>
          <option value="0"${fields.new_provision==0?' selected':''}>No</option>
        </select></div>`;
      html += `<div class="col-md-3"><label class="form-label">Deep</label>
        <select name="deep" class="form-select">
          <option value="1"${fields.deep==1?' selected':''}>Yes</option>
          <option value="0"${fields.deep==0?' selected':''}>No</option>
        </select></div>`;
      html += `<div class="col-md-3"><label class="form-label">Zabbix Agent</label>
        <select name="zabbix_agent" class="form-select">
          <option value="1"${fields.zabbix_agent==1?' selected':''}>Yes</option>
          <option value="0"${fields.zabbix_agent==0?' selected':''}>No</option>
        </select></div>`;
      html += `<div class="col-md-3"><label class="form-label">CrowdStrike</label>
        <select name="crowdstrike" class="form-select">
          <option value="1"${fields.crowdstrike==1?' selected':''}>Yes</option>
          <option value="0"${fields.crowdstrike==0?' selected':''}>No</option>
        </select></div>`;
      html += `<div class="col-md-3"><label class="form-label">Template</label>
        <select name="template" class="form-select">
          <option value="1"${fields.template==1?' selected':''}>Yes</option>
          <option value="0"${fields.template==0?' selected':''}>No</option>
        </select></div>`;
      html += `<div class="col-md-3"><label class="form-label">EOS</label>
        <input type="text" name="eos" class="form-control" value="${fields.eos}"></div>`;
      html += `<div class="col-md-3"><label class="form-label">STD / NON STD</label>
        <input type="text" name="std_nonstd" class="form-control" value="${fields.std_nonstd}"></div>`;
      html += `<div class="col-md-12"><label class="form-label">Other</label>
        <input type="text" name="other" class="form-control" value="${fields.other}"></div>`;
      document.getElementById('editOSFormBody').innerHTML = html;
    });
  });

  // Delete: Confirm then Redirect
  document.querySelectorAll('.delete-btn').forEach(btn=>{
    btn.addEventListener('click', function(){
      if(confirm('Delete this OS record?')) {
        window.location = "os-support-delete.php?id=" + this.dataset.id;
      }
    });
  });
});
<?php else: ?>
document.addEventListener('DOMContentLoaded', function(){
  renderColOrderUI();
  applyColOrder();
  let sortableCol = new Sortable(document.getElementById('os-column-list'), {
    animation: 150
  });
  document.getElementById('saveColumnOrder').onclick = function() {
    let order = Array.from(document.querySelectorAll('#os-column-list .list-group-item')).map(el=>el.dataset.key);
    setColOrder(order);
    applyColOrder();
    var canvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('osColumnsCanvas'));
    canvas.hide();
  };
});
<?php endif; ?>
</script>
</body>
</html>