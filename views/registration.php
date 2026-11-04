<?php
ob_start();
?>
<style>
.register-page{max-width:980px;margin:32px auto;padding:0 20px}.register-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px}.register-card h1{margin:0 0 8px}.register-note{color:#64748b;margin-bottom:24px}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.form-group.full{grid-column:1/-1}.form-group label{display:block;font-weight:700;margin-bottom:7px}.form-control{width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:10px 12px}.register-section{border-top:1px solid #e2e8f0;margin-top:24px;padding-top:20px}.register-section h3{margin-top:0}.otp-row{display:grid;grid-template-columns:1fr auto;gap:10px}.alert-success{background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;padding:12px;border-radius:8px;margin-bottom:18px}.alert-danger{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;padding:12px;border-radius:8px;margin-bottom:18px}@media(max-width:700px){.form-grid{grid-template-columns:1fr}.otp-row{grid-template-columns:1fr}.form-group.full{grid-column:auto}}
</style>
<div class="register-page">
  <div class="register-card">
    <h1>Đăng ký hồ sơ nội trú mới</h1>
    <p class="register-note">Trang công khai - không cần tài khoản trước. MSSV do trường cung cấp sẽ là tên đăng nhập; hệ thống tự tạo mật khẩu và gửi thông tin tài khoản đến email cá nhân.</p>
    <?php if (!empty($success)): ?><div class="alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form action="<?= htmlspecialchars(parse_url(url('register'), PHP_URL_PATH), ENT_QUOTES, 'UTF-8') ?>" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group"><label>Loại đăng ký</label><select name="registrationType" class="form-control"><option>Đăng ký thường</option><option>Đăng ký diện miễn giảm lệ phí phòng ở</option></select></div>
        <div class="form-group"><label>Trường đại học</label><select name="university" class="form-control" required><option value="">-- Chọn trường --</option><?php if (!empty($universities) && is_array($universities)): ?><?php foreach ($universities as $u): ?><option value="<?= htmlspecialchars($u['MaTruong']) ?>"><?= htmlspecialchars($u['TenTruong']) ?></option><?php endforeach; ?><?php endif; ?></select></div>
        <div class="form-group"><label>Họ và tên</label><input name="hoTen" id="hoTen" class="form-control" required></div>
        <div class="form-group"><label>MSSV</label><input name="mssv" class="form-control" maxlength="30" placeholder="MSSV do trường cấp" required></div>
        <div class="form-group"><label>Số CCCD</label><input name="cccd" class="form-control" maxlength="15" required></div>
        <div class="form-group"><label>Ngày sinh</label><input type="date" name="birthday" class="form-control"></div>
        <div class="form-group"><label>Giới tính</label><select name="gender" id="gender" class="form-control" required><option value="">-- Chọn giới tính --</option><option value="Nam">Nam</option><option value="Nữ">Nữ</option></select></div>
        <div class="form-group"><label>Số điện thoại</label><input name="phone" class="form-control" maxlength="15"></div>
        <div class="form-group"><label>Số BHYT</label><input name="bhyt" class="form-control"></div>
        <div class="form-group"><label>Email cá nhân</label><input type="email" name="email" class="form-control" maxlength="150" placeholder="email@example.com" required></div>
      </div>
      <div class="register-section"><h3>Chọn chỗ ở</h3><div class="form-grid">
        <div class="form-group"><label>Khu</label><select id="area" name="area" class="form-control" required><option value="">-- Chọn khu --</option><?php foreach (($areas ?? []) as $a): ?><option value="<?= htmlspecialchars($a['MaKhu']) ?>"><?= htmlspecialchars($a['TenKhu']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Loại phòng</label><select id="roomType" name="roomType" class="form-control"><option value="">-- Tất cả loại phòng --</option><?php if (!empty($roomTypes) && is_array($roomTypes)): ?><?php foreach ($roomTypes as $r): ?><option value="<?= htmlspecialchars($r['MaLoaiPhong']) ?>"><?= htmlspecialchars($r['TenLoaiPhong']) ?></option><?php endforeach; ?><?php endif; ?></select></div>
        <div class="form-group"><label>Tòa nhà</label><select id="building" class="form-control" required><option value="">-- Chọn tòa --</option></select></div>
        <div class="form-group"><label>Tầng</label><select id="floor" class="form-control" required><option value="">-- Chọn tầng --</option></select></div>
        <div class="form-group"><label>Phòng</label><select id="room" class="form-control" required><option value="">-- Chọn phòng --</option></select></div>
        <div class="form-group"><label>Giường trống</label><select name="bed" id="bed" class="form-control" required><option value="">-- Chọn giường --</option></select></div>
      </div></div>
      <p id="accommodationHint" class="register-note" style="margin:14px 0 0;min-height:20px"></p>
      <div class="register-section"><h3>Hồ sơ giấy tờ</h3><div class="form-grid">
        <div class="form-group"><label>Ảnh CCCD mặt trước</label><input type="file" name="cccdFront" accept="image/jpeg,image/png,image/webp" class="form-control"></div>
        <div class="form-group"><label>Ảnh CCCD mặt sau</label><input type="file" name="cccdBack" accept="image/jpeg,image/png,image/webp" class="form-control"></div>
        <div class="form-group"><label>Ảnh thẻ 4x6</label><input type="file" name="portrait" accept="image/jpeg,image/png,image/webp" class="form-control"></div>
        <div class="form-group"><label>Thẻ BHYT</label><input type="file" name="bhytImage" accept="image/jpeg,image/png,image/webp" class="form-control"></div>
        <div class="form-group full"><label>Giấy xác nhận nhập học</label><input type="file" name="admissionLetter" accept="image/jpeg,image/png,image/webp" class="form-control"></div>
      </div></div>
      <button class="btn btn-primary" type="submit" style="margin-top:24px">Lưu & gửi hồ sơ</button>
    </form>
  </div>
</div>
<script>
(function(){
 const optionsUrl=<?= json_encode(url('register/options'), JSON_UNESCAPED_SLASHES) ?>;
 const $=id=>document.getElementById(id);
 const requestVersions={};
 function setHint(message='',isError=false){
  const hint=$('accommodationHint');
  hint.textContent=message;
  hint.style.color=isError?'#b91c1c':'#64748b';
 }
 function resetSelect(select,placeholder){
  select.innerHTML='';
  select.add(new Option(placeholder,''));
 }
 function setLoading(select,loading){
  select.disabled=loading;
  select.setAttribute('aria-busy',loading?'true':'false');
 }
 function invalidate(type){requestVersions[type]=(requestVersions[type]||0)+1;}
 async function load(type,params,target,field,placeholder,getLabel){
  const version=requestVersions[type]=(requestVersions[type]||0)+1;
  resetSelect(target,placeholder);
  setLoading(target,true);
const csrfInput = document.querySelector('input[name="csrf_token"]') || document.querySelector('input[name="_token"]');
const csrfToken = csrfInput ? csrfInput.value : '';
 params = Object.assign({ type: type, csrf_token: csrfToken }, params);
 const body=new URLSearchParams();
  Object.keys(params).forEach(key=>body.append(key,params[key] ?? ''));
  try{
   const response=await fetch(optionsUrl,{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:body.toString(),
    credentials:'same-origin',
    cache:'no-store'
   });
   if(!response.ok) throw new Error('HTTP '+response.status);
   const data=await response.json();
   if(!Array.isArray(data.values)) throw new Error(data.error || 'Phản hồi dữ liệu không hợp lệ');
   if(version!==requestVersions[type])return [];
   if(data.values.length===0){target.add(new Option('-- Không có lựa chọn phù hợp --',''));}
   data.values.forEach(row=>{
    const value=row[field];
    const label=getLabel ? getLabel(row,value) : String(value);
    target.add(new Option(label,String(value)));
   });
   return data.values;
  }catch(error){
   console.error(error);
   if(version===requestVersions[type])target.add(new Option('-- Không tải được dữ liệu, vui lòng thử lại --',''));
   return [];
  }finally{
   if(version===requestVersions[type])setLoading(target,false);
  }
 }
 async function loadBuilding(){
  invalidate('floors');invalidate('rooms');invalidate('beds');
  resetSelect($('floor'),'-- Chọn tầng --');
  resetSelect($('room'),'-- Chọn phòng --');
  resetSelect($('bed'),'-- Chọn giường --');
  setHint('');
  if(!$('area').value || !$('gender').value){resetSelect($('building'),'-- Chọn tòa --');setHint('Vui lòng chọn khu và giới tính để tải danh sách tòa nhà.');return;}
  const rows=await load('buildings',{area:$('area').value,gender:$('gender').value},$('building'),'MaToaNha','-- Chọn tòa --',(row)=>row.TenToaNha);
  if(rows.length===0){setHint('Không có tòa nhà phù hợp với khu và giới tính đã chọn.',true);return;}
  if(rows.length===1){$('building').value=rows[0].MaToaNha;await loadFloor();}
 }
 async function loadFloor(){
  invalidate('rooms');invalidate('beds');
  resetSelect($('room'),'-- Chọn phòng --');
  resetSelect($('bed'),'-- Chọn giường --');
  if(!$('building').value)return;
  const rows=await load('floors',{building:$('building').value,roomType:$('roomType').value},$('floor'),'Tang','-- Chọn tầng --',(row)=>'Tầng '+row.Tang);
  if(rows.length===0){setHint('Tòa này hiện không còn tầng/phòng có giường trống phù hợp.',true);return;}
  if(rows.length===1){$('floor').value=String(rows[0].Tang);await loadRoom();}
 }
 async function loadRoom(){
  invalidate('beds');
  resetSelect($('bed'),'-- Chọn giường --');
  if(!$('building').value || !$('floor').value)return;
  const rows=await load('rooms',{building:$('building').value,floor:$('floor').value,roomType:$('roomType').value},$('room'),'MaPhong','-- Chọn phòng --',(row)=>row.SoPhong);
  if(rows.length===0){setHint('Không có phòng trống phù hợp ở tầng đã chọn.',true);return;}
  if(rows.length===1){$('room').value=rows[0].MaPhong;await loadBed();}
 }
 async function loadBed(){
  if(!$('room').value){invalidate('beds');resetSelect($('bed'),'-- Chọn giường --');return;}
  const rows=await load('beds',{room:$('room').value},$('bed'),'MaGiuong','-- Chọn giường --',(row)=>row.KyHieuGiuong);
  if(rows.length===0){setHint('Phòng này không còn giường trống. Vui lòng chọn phòng khác.',true);return;}
  if(rows.length===1){$('bed').value=rows[0].MaGiuong;}
  setHint(rows.length>1?'Đã tải danh sách giường trống. Vui lòng chọn một giường.':'Đã tự chọn giường trống duy nhất.');
 }
 $('area').addEventListener('change',loadBuilding);
 $('gender').addEventListener('change',loadBuilding);
 $('roomType').addEventListener('change',async function(){await loadFloor();});
 $('building').addEventListener('change',loadFloor);
 $('floor').addEventListener('change',loadRoom);
 $('room').addEventListener('change',loadBed);
 if($('area').value && $('gender').value){loadBuilding();}
})();
</script>
<?php
$content=ob_get_clean();
$title='Đăng ký hồ sơ nội trú';
require __DIR__.'/layouts/main.php';
?>
