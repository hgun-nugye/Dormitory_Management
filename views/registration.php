<?php
ob_start();
$studentPrefill = $studentPrefill ?? null;
?>
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

 (async function checkUrlPreselection() {
   const urlParams = new URLSearchParams(window.location.search);
   const targetArea = urlParams.get('area');
   const targetBuilding = urlParams.get('building');
   const targetFloor = urlParams.get('floor');
   const targetRoom = urlParams.get('room') || urlParams.get('maPhong');
   const targetGender = urlParams.get('gender');
   const targetRoomType = urlParams.get('roomType');
   const targetUni = urlParams.get('university');

   if (!targetArea && !targetRoom && !targetBuilding) return;

   if (targetUni) {
     const uEl = document.querySelector('[name="university"]');
     if (uEl) {
       for (let opt of uEl.options) {
         if (opt.value === targetUni || opt.text.toUpperCase().includes(targetUni.toUpperCase())) {
           uEl.value = opt.value;
           break;
         }
       }
     }
   }

   if (targetGender && $('gender')) {
     for (let opt of $('gender').options) {
       if (opt.value.toLowerCase() === targetGender.toLowerCase() || opt.text.toLowerCase() === targetGender.toLowerCase()) {
         $('gender').value = opt.value;
         break;
       }
     }
   }

   if (targetArea && $('area')) {
     for (let opt of $('area').options) {
       if (opt.value === targetArea || opt.text.includes(targetArea)) {
         $('area').value = opt.value;
         break;
       }
     }
   }

   if (targetRoomType && $('roomType')) {
     for (let opt of $('roomType').options) {
       if (opt.value === targetRoomType || opt.text.includes(targetRoomType)) {
         $('roomType').value = opt.value;
         break;
       }
     }
   }

   if ($('area').value && $('gender').value) {
     await loadBuilding();
     if (targetBuilding && $('building')) {
       for (let opt of $('building').options) {
         if (opt.value === targetBuilding || opt.text.includes(targetBuilding) || targetBuilding.includes(opt.value)) {
           $('building').value = opt.value;
           break;
         }
       }

       if ($('building').value) {
         await loadFloor();
         if (targetFloor && $('floor')) {
           $('floor').value = String(targetFloor);
           await loadRoom();

           if (targetRoom && $('room')) {
             for (let opt of $('room').options) {
               const normOpt = opt.value.replace('.', '-');
               const normTarget = targetRoom.replace('.', '-');
               if (opt.value === targetRoom || opt.text === targetRoom || normOpt === normTarget || opt.text.includes(targetRoom)) {
                 $('room').value = opt.value;
                 break;
               }
             }

             if ($('room').value) {
               await loadBed();
             }
           }
         }
       }
     }

     if (targetRoom) {
       setHint('✓ Đã tự động chọn phòng ' + targetRoom + ' theo yêu cầu giữ chỗ 72h. Vui lòng hoàn tất thông tin cá nhân và gửi hồ sơ.');
       const hintEl = $('accommodationHint');
       if (hintEl) {
         hintEl.style.color = '#0284c7';
         hintEl.style.fontWeight = 'bold';
       }
     }
   }
 })();

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
