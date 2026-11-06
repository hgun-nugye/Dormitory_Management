<?php
ob_start();
$studentPrefill = $studentPrefill ?? null;
?>
<style>
.register-page{--reg-ink:#102a43;--reg-muted:#63758b;--reg-blue:#1769aa;max-width:1080px;margin:42px auto 64px;padding:0 22px;color:var(--reg-ink)}
.register-hero{position:relative;overflow:hidden;padding:34px 38px 30px;border-radius:22px 22px 0 0;background:linear-gradient(118deg,#0b3154 0%,#12649a 58%,#1684a0 100%);color:#fff}
.register-hero:after{content:"";position:absolute;width:270px;height:270px;right:-55px;top:-150px;border:1px solid #ffffff2b;border-radius:50%;box-shadow:0 0 0 32px #ffffff0d,0 0 0 66px #ffffff0a}
.register-eyebrow{display:inline-flex;align-items:center;gap:9px;margin-bottom:13px;color:#b9e6fa;font-size:.75rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
.register-eyebrow:before{content:"";width:24px;height:2px;background:#71d0e7}
.register-hero h1{position:relative;z-index:1;margin:0 0 10px;color:#fff;font-size:clamp(1.7rem,3vw,2.35rem);letter-spacing:-.035em;line-height:1.15}
.register-hero p{position:relative;z-index:1;max-width:760px;margin:0;color:#d9edf7;line-height:1.7;font-size:.96rem}
.register-card{background:#fff;border:1px solid #dce6ef;border-top:0;border-radius:0 0 22px 22px;padding:30px 38px 38px;box-shadow:0 20px 55px #173b5910}
.register-progress{display:flex;align-items:center;gap:12px;padding:0 0 24px;border-bottom:1px solid #e8eef4;color:#64758a;font-size:.83rem;font-weight:650}
.progress-step{display:flex;align-items:center;gap:8px;white-space:nowrap}.progress-dot{display:grid;width:27px;height:27px;place-items:center;border-radius:50%;background:#e8f4fb;color:#1769aa;font-size:.75rem;font-weight:800}.progress-line{height:1px;flex:1;background:#dfe8f0}
.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 22px}.form-group{min-width:0}.form-group.full{grid-column:1/-1}.form-group label{display:block;margin-bottom:8px;color:#263d53;font-size:.88rem;font-weight:700}.form-group label:has(+ input:required)::after,.form-group label:has(+ select:required)::after{content:" *";color:#d04444}
.form-control{box-sizing:border-box;display:block;width:100%;min-height:46px;border:1px solid #ccd8e3;border-radius:10px;background:#fbfdff;padding:10px 13px;color:#19334a;font:inherit;font-size:.92rem;transition:border-color .18s,box-shadow .18s,background .18s}.form-control:hover{border-color:#9eb6ca;background:#fff}.form-control:focus{outline:0;border-color:#2783bd;background:#fff;box-shadow:0 0 0 4px #2783bd1c}.form-control::placeholder{color:#9aaabb}.form-control[type=file]{padding:7px;background:#f8fbfd;color:#63758b;font-size:.83rem}.form-control[type=file]::file-selector-button{margin-right:12px;border:0;border-radius:7px;background:#e7f2f9;padding:7px 10px;color:#1769aa;font-weight:700;cursor:pointer}
.register-section{margin-top:30px;padding-top:25px;border-top:1px solid #e7edf3}.section-heading{display:flex;align-items:center;gap:13px;margin-bottom:20px}.section-number{display:grid;width:38px;height:38px;flex:0 0 38px;place-items:center;border:1px solid #d4e9f5;border-radius:12px;background:#eff8fc;color:#1769aa;font-size:.83rem;font-weight:850}.section-heading h3{margin:0 0 3px;color:#17344e;font-size:1.08rem;letter-spacing:-.015em}.section-heading p{margin:0;color:#7a8a9b;font-size:.82rem}.register-note{color:var(--reg-muted);margin-bottom:24px;line-height:1.65}.accommodation-hint{min-height:24px;margin:17px 0 0;padding:10px 13px;border-radius:9px;background:#f5f9fc;color:#63758b;font-size:.86rem;line-height:1.5}.alert-success,.alert-danger{padding:14px 16px;border-radius:11px;margin-bottom:20px;line-height:1.55}.alert-success{background:#ecfdf5;color:#047857;border:1px solid #a7f3d0}.alert-danger{background:#fff5f4;color:#b42318;border:1px solid #f4c7c3}.register-submit{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-top:30px;padding-top:24px;border-top:1px solid #e7edf3}.register-submit p{margin:0;color:#75869a;font-size:.82rem;line-height:1.5}.register-submit .btn{min-height:48px;padding:0 24px;border:0;border-radius:10px;background:linear-gradient(115deg,#1769aa,#12809e);box-shadow:0 8px 18px #1769aa2b;font-weight:750;letter-spacing:.01em;transition:transform .18s,box-shadow .18s}.register-submit .btn:hover{transform:translateY(-1px);box-shadow:0 11px 22px #1769aa38}
.email-otp-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px}.email-otp-row .form-control{min-width:0}.email-otp-send{min-height:46px;padding:0 15px;border:1px solid #b9d4e5;border-radius:10px;background:#eef7fc;color:#15577f;font:inherit;font-size:.84rem;font-weight:750;cursor:pointer}.email-otp-send:disabled{opacity:.65;cursor:wait}.email-otp-status{min-height:20px;margin:7px 0 0;color:#63758b;font-size:.8rem;line-height:1.4}
@media(max-width:700px){.register-page{margin:22px auto 40px;padding:0 14px}.register-hero{padding:27px 22px 24px;border-radius:17px 17px 0 0}.register-hero p{font-size:.9rem}.register-card{padding:22px 20px 26px;border-radius:0 0 17px 17px}.register-progress{gap:8px;padding-bottom:19px;font-size:.72rem}.progress-step{gap:6px}.progress-dot{width:24px;height:24px}.form-grid{grid-template-columns:1fr;gap:17px}.form-group.full{grid-column:auto}.register-section{margin-top:24px;padding-top:21px}.register-submit{align-items:stretch;flex-direction:column-reverse;gap:13px}.register-submit .btn{width:100%}.email-otp-row{grid-template-columns:1fr}.email-otp-send{width:100%}}
</style>
<div class="register-page">
  <div class="register-hero">
    <div class="register-eyebrow">Cổng đăng ký nội trú · ĐHQG-HCM</div>
    <h1>Đăng ký hồ sơ nội trú</h1>
    <p>T&#7921; t&#7841;o t&#224;i kho&#7843;n, ho&#224;n thi&#7879;n h&#7891; s&#417; sinh vi&#234;n, ch&#7885;n ch&#7895; &#7903; ph&#249; h&#7907;p v&#224; x&#225;c th&#7921;c email b&#7857;ng m&#227; OTP.</p>
  </div>
  <div class="register-card">
    <div class="register-progress" aria-label="Các phần của biểu mẫu">
      <span class="progress-step"><span class="progress-dot">01</span> Thông tin</span><span class="progress-line"></span>
      <span class="progress-step"><span class="progress-dot">02</span> Chỗ ở</span><span class="progress-line"></span>
      <span class="progress-step"><span class="progress-dot">03</span> Giấy tờ</span>
    </div>
    <p class="register-note" style="margin:20px 0 22px">Tên đăng nhập do bạn tự chọn. Email được dùng để nhận thông báo hồ sơ và hỗ trợ khôi phục tài khoản.</p>
    <?php if (!empty($success)): ?><div class="alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (empty($isOpen)): ?>
      <section style="padding:28px;background:#fff7ed;border:1px solid #fed7aa;border-radius:14px;text-align:center" role="status">
        <h2 style="margin:0 0 10px;color:#9a3412">Cổng đăng ký nội trú hiện đang đóng</h2>
        <p style="margin:0 auto 18px;max-width:650px;color:#64748b">Hệ thống sẽ tiếp nhận hồ sơ trong thời gian của đợt đăng ký. Bạn có thể xem lịch đợt gần nhất bên dưới.</p>
        <?php if (!empty($upcomingDot)): ?>
          <div style="margin:0 auto 18px;max-width:620px;padding:16px;text-align:left;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;color:#1e3a8a">
            <strong><?= htmlspecialchars($upcomingDot['TenDot']) ?></strong>
            <div>Năm học: <?= htmlspecialchars($upcomingDot['NamHoc']) ?></div>
            <div>Mở cổng: <?= date('H:i d/m/Y', strtotime($upcomingDot['ThoiGianMoCong'])) ?></div>
            <div>Đóng cổng: <?= date('H:i d/m/Y', strtotime($upcomingDot['ThoiGianDongCong'])) ?></div>
          </div>
        <?php elseif (!empty($latestClosedDot)): ?>
          <p style="color:#475569">Đợt gần nhất <strong><?= htmlspecialchars($latestClosedDot['TenDot']) ?></strong> đã đóng vào <?= date('H:i d/m/Y', strtotime($latestClosedDot['ThoiGianDongCong'])) ?>.</p>
        <?php endif; ?>
        <a class="btn btn-primary" href="<?= url('student/dashboard') ?>">Quay lại cổng sinh viên</a>
      </section>
    <?php else: ?>
      <div style="margin-bottom:20px;padding:14px 18px;background:#f0fdf4;border:1px solid #86efac;border-radius:12px;color:#166534">
        <strong><?= htmlspecialchars($activeDot['TenDot']) ?></strong> · <?= htmlspecialchars($activeDot['NamHoc']) ?>
        <span>— Nhận hồ sơ đến <?= date('H:i d/m/Y', strtotime($activeDot['ThoiGianDongCong'])) ?></span>
      </div>
    <?php if (is_array($studentPrefill)): ?><div class="register-note" style="padding:14px 18px;margin:0 0 20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;color:#1e3a8a">Thông tin hồ sơ đã được điền từ tài khoản của bạn. Hãy xác thực email, chọn chỗ ở và gửi hồ sơ giữ chỗ.</div><?php endif; ?>
    <form action="<?= htmlspecialchars(parse_url(url('register'), PHP_URL_PATH), ENT_QUOTES, 'UTF-8') ?>" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="register-section" style="margin-top:0;padding-top:0;border-top:0"><div class="section-heading"><span class="section-number">01</span><div><h3>Tài khoản & thông tin cá nhân</h3><p>Chọn tên đăng nhập, mật khẩu và cung cấp thông tin định danh</p></div></div><div class="form-grid">
        <div class="form-group"><label>Tên đăng nhập</label><input name="username" class="form-control" maxlength="50" autocomplete="username" placeholder="Tên đăng nhập bạn muốn dùng" required></div>
        <div class="form-group"><label>Mật khẩu</label><input type="password" name="password" class="form-control" minlength="6" autocomplete="new-password" placeholder="Tối thiểu 6 ký tự" required></div>
        <div class="form-group"><label>Nhập lại mật khẩu</label><input type="password" name="confirm_password" class="form-control" minlength="6" autocomplete="new-password" placeholder="Nhập lại mật khẩu" required></div>
        <div class="form-group"><label>Loại đăng ký</label><select name="registrationType" class="form-control"><option>Đăng ký thường</option><option>Đăng ký diện miễn giảm lệ phí phòng ở</option></select></div>
        <div class="form-group"><label>Trường đại học</label><select name="university" class="form-control" required><option value="">-- Chọn trường --</option><?php if (!empty($universities) && is_array($universities)): ?><?php foreach ($universities as $u): ?><option value="<?= htmlspecialchars($u['MaTruong']) ?>"><?= htmlspecialchars($u['TenTruong']) ?></option><?php endforeach; ?><?php endif; ?></select></div>
        <div class="form-group"><label>Họ và tên</label><input name="hoTen" id="hoTen" class="form-control" required></div>
        <div class="form-group"><label>MSSV</label><input name="mssv" class="form-control" maxlength="30" pattern="[A-Za-z0-9]+" title="MSSV chỉ gồm chữ cái không dấu và chữ số" placeholder="MSSV do trường cấp" required></div>
        <div class="form-group"><label>Số CCCD</label><input name="cccd" class="form-control" inputmode="numeric" maxlength="12" minlength="12" pattern="[0-9]{12}" title="CCCD phải gồm đủ 12 chữ số" required></div>
        <div class="form-group"><label>Ngày sinh</label><input type="date" name="birthday" class="form-control"></div>
        <div class="form-group"><label>Giới tính</label><select name="gender" id="gender" class="form-control" required><option value="">-- Chọn giới tính --</option><option value="Nam">Nam</option><option value="Nữ">Nữ</option></select></div>
        <div class="form-group"><label>Số điện thoại</label><input name="phone" class="form-control" inputmode="numeric" maxlength="11" minlength="9" pattern="[0-9]{9,11}" title="Số điện thoại gồm từ 9 đến 11 chữ số" required></div>
        <div class="form-group"><label>Số BHYT</label><input name="bhyt" class="form-control"></div>
        <div class="form-group full"><label for="registerEmail">Email cá nhân</label><div class="email-otp-row"><input id="registerEmail" type="email" name="email" class="form-control" maxlength="100" autocomplete="email" placeholder="email@example.com" required><button type="button" class="email-otp-send" id="sendEmailOtp">Gửi mã OTP</button></div><p class="email-otp-status" id="emailOtpStatus" aria-live="polite">Gửi mã xác thực đến email rồi nhập mã bên dưới.</p></div>
        <div class="form-group"><label for="emailOtp">Mã xác thực email</label><input id="emailOtp" type="text" name="email_otp" class="form-control" inputmode="numeric" maxlength="6" minlength="6" pattern="[0-9]{6}" placeholder="Nhập mã 6 chữ số" required></div>
      </div></div>
      <div class="register-section"><div class="section-heading"><span class="section-number">02</span><div><h3>Chọn chỗ ở</h3><p>Chọn khu và loại phòng để tìm giường còn trống</p></div></div><div class="form-grid">
        <div class="form-group"><label>Khu</label><select id="area" name="area" class="form-control" required><option value="">-- Chọn khu --</option><?php foreach (($areas ?? []) as $a): ?><option value="<?= htmlspecialchars($a['MaKhu']) ?>"><?= htmlspecialchars($a['TenKhu']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Loại phòng</label><select id="roomType" name="roomType" class="form-control"><option value="">-- Tất cả loại phòng --</option><?php if (!empty($roomTypes) && is_array($roomTypes)): ?><?php foreach ($roomTypes as $r): ?><option value="<?= htmlspecialchars($r['MaLoaiPhong']) ?>"><?= htmlspecialchars($r['TenLoaiPhong']) ?></option><?php endforeach; ?><?php endif; ?></select></div>
        <div class="form-group"><label>Tòa nhà</label><select id="building" name="building" class="form-control" required><option value="">-- Chọn tòa --</option></select></div>
        <div class="form-group"><label>Tầng</label><select id="floor" name="floor" class="form-control" required><option value="">-- Chọn tầng --</option></select></div>
        <div class="form-group"><label>Phòng</label><select id="room" name="room" class="form-control" required><option value="">-- Chọn phòng --</option></select></div>
        <div class="form-group"><label>Giường trống</label><select name="bed" id="bed" class="form-control" required><option value="">-- Chọn giường --</option></select></div>
      </div></div>
      <p id="accommodationHint" class="accommodation-hint" aria-live="polite"></p>
      <div class="register-section"><div class="section-heading"><span class="section-number">03</span><div><h3>Hồ sơ giấy tờ</h3><p>Tải lên ảnh rõ nét để hồ sơ được xét duyệt nhanh hơn</p></div></div><div class="form-grid">
        <div class="form-group"><label>Ảnh CCCD mặt trước</label><input type="file" name="cccdFront" accept="image/jpeg,image/png,image/webp" class="form-control"></div>
        <div class="form-group"><label>Ảnh CCCD mặt sau</label><input type="file" name="cccdBack" accept="image/jpeg,image/png,image/webp" class="form-control"></div>
        <div class="form-group"><label>Ảnh thẻ 4x6</label><input type="file" name="portrait" accept="image/jpeg,image/png,image/webp" class="form-control"></div>
        <div class="form-group"><label>Thẻ BHYT</label><input type="file" name="bhytImage" accept="image/jpeg,image/png,image/webp" class="form-control"></div>
        <div class="form-group full"><label>Giấy xác nhận nhập học</label><input type="file" name="admissionLetter" accept="image/jpeg,image/png,image/webp" class="form-control"></div>
      </div></div>
      <div class="register-submit"><p>Vui lòng kiểm tra lại thông tin trước khi gửi hồ sơ.</p><button class="btn btn-primary" type="submit">Gửi hồ sơ đăng ký <span aria-hidden="true">→</span></button></div>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php if (!empty($isOpen)): ?>
<script>
(function(){
 const studentPrefill=<?= json_encode($studentPrefill, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: 'null' ?>;
 if(studentPrefill){
  const map={hoTen:'HoVaTen',mssv:'MaSinhVien',cccd:'SoCCCD',birthday:'NgaySinh',gender:'GioiTinh',phone:'SoDienThoai',bhyt:'MaSoBHYT',registerEmail:'Email'};
  Object.entries(map).forEach(([id,key])=>{const el=document.getElementById(id)||document.querySelector(`[name="${id}"]`);if(el&&studentPrefill[key]){el.value=studentPrefill[key];if(el.tagName==='INPUT')el.readOnly=true;else el.disabled=true;}});
  const university=document.querySelector('[name="university"]');if(university&&studentPrefill.MaTruong){university.value=studentPrefill.MaTruong;university.disabled=true;}
  ['username','password','confirm_password'].forEach(name=>{const el=document.querySelector(`[name="${name}"]`);if(el){el.required=false;el.closest('.form-group').hidden=true;}});
 }
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
  if(!$('area').value || !$('gender').value){
    resetSelect($('building'),'-- Chọn tòa --');
    if(!$('gender').value && !$('area').value) {
      setHint('Vui lòng chọn giới tính (ở mục thông tin phía trên) và khu KTX để tải danh sách tòa nhà.');
    } else if(!$('gender').value) {
      setHint('Vui lòng chọn giới tính ở mục thông tin cá nhân phía trên.');
    } else {
      setHint('Vui lòng chọn khu KTX (Khu A hoặc Khu B).');
    }
    return;
  }
  const rows=await load('buildings',{area:$('area').value,gender:$('gender').value,roomType:$('roomType').value},$('building'),'MaToaNha','-- Chọn tòa --',(row)=>row.TenToaNha);
  if(rows.length===0){
    setHint('Không có tòa nhà phù hợp với khu, giới tính hoặc loại phòng đã chọn.',true);
    return;
  }
  if(rows.length===1){
    $('building').value=rows[0].MaToaNha;
    setHint('Đã tự chọn tòa duy nhất phù hợp: ' + rows[0].TenToaNha);
    await loadFloor();
  } else {
    setHint('Đã tìm thấy ' + rows.length + ' tòa nhà phù hợp. Vui lòng chọn một tòa nhà.');
  }
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
 $('roomType').addEventListener('change',async function(){
   if(!$('building').value){
     await loadBuilding();
   } else {
     await loadFloor();
   }
 });
 $('building').addEventListener('change',loadFloor);
 $('floor').addEventListener('change',loadRoom);
 $('room').addEventListener('change',loadBed);
 if($('area').value && $('gender').value){loadBuilding();}

 const otpButton=$('sendEmailOtp');
 const emailInput=$('registerEmail');
 const otpInput=$('emailOtp');
 const otpStatus=$('emailOtpStatus');
 const otpUrl=<?= json_encode(url('register/send-otp'), JSON_UNESCAPED_SLASHES) ?>;
 let otpSentEmail='';
 emailInput.addEventListener('input',function(){
  if(emailInput.value.trim().toLowerCase()!==otpSentEmail){
   otpInput.value='';
   if(otpSentEmail)otpStatus.textContent='Email đã thay đổi. Hãy gửi mã mới đến email hiện tại.';
  }
 });
 otpButton.addEventListener('click',async function(){
  if(!emailInput.checkValidity()){emailInput.reportValidity();return;}
  const csrfInput=document.querySelector('input[name="csrf_token"]');
  const body=new URLSearchParams({email:emailInput.value.trim(),csrf_token:csrfInput?csrfInput.value:''});
  otpButton.disabled=true;
  otpButton.textContent='Đang gửi mã...';
  otpStatus.textContent='Đang kết nối máy chủ email...';
  try{
   const response=await fetch(otpUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString(),credentials:'same-origin',cache:'no-store'});
   const result=await response.json();
   otpStatus.textContent=result.message||'Không gửi được mã xác thực.';
   otpStatus.style.color=result.success?'#047857':'#b42318';
   if(!result.success){otpButton.disabled=false;otpButton.textContent='Gửi mã OTP';return;}
   otpSentEmail=emailInput.value.trim().toLowerCase();
   otpInput.value='';
   let remaining=60;
   otpButton.textContent='Gửi lại sau '+remaining+' giây';
   const timer=setInterval(function(){
    remaining--;
    if(remaining<=0){clearInterval(timer);otpButton.disabled=false;otpButton.textContent='Gửi lại mã OTP';}
    else otpButton.textContent='Gửi lại sau '+remaining+' giây';
   },1000);
  }catch(error){
   otpStatus.textContent='Không kết nối được máy chủ email. Vui lòng thử lại.';
   otpStatus.style.color='#b42318';
   otpButton.disabled=false;
   otpButton.textContent='Gửi mã OTP';
  }
 });
})();
</script>
<?php endif; ?>
<?php
$content=ob_get_clean();
$title='Đăng ký hồ sơ nội trú';
require __DIR__.'/layouts/main.php';
?>
