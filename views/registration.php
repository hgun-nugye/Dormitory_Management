<?php
ob_start();
?>
<style>
.register-page{max-width:980px;margin:32px auto;padding:0 20px}.register-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px}.register-card h1{margin:0 0 8px}.register-note{color:#64748b;margin-bottom:24px}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.form-group.full{grid-column:1/-1}.form-group label{display:block;font-weight:700;margin-bottom:7px}.form-control{width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:10px 12px}.register-section{border-top:1px solid #e2e8f0;margin-top:24px;padding-top:20px}.register-section h3{margin-top:0}.otp-row{display:grid;grid-template-columns:1fr auto;gap:10px}.alert-success{background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;padding:12px;border-radius:8px;margin-bottom:18px}.alert-danger{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;padding:12px;border-radius:8px;margin-bottom:18px}@media(max-width:700px){.form-grid{grid-template-columns:1fr}.otp-row{grid-template-columns:1fr}.form-group.full{grid-column:auto}}
</style>
<div class="register-page">
  <div class="register-card">
    <h1>Đăng ký hồ sơ nội trú mới</h1>
    <p class="register-note">Trang công khai - không cần tài khoản trước. MSSV do trường cung cấp sẽ là tên đăng nhập; hệ thống tự động tạo tài khoản với mật khẩu ngẫu nhiên 8 ký tự và gửi về email cá nhân kèm yêu cầu đổi mật khẩu sau khi đăng nhập.</p>

    <?php if (!empty($success)): ?><div class="alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if (empty($isOpen)): ?>
      <!-- ======================================================= -->
      <!-- GIAO DIỆN KHI CỔNG ĐĂNG KÝ ĐANG ĐÓNG HOẶC CHƯA MỞ        -->
      <!-- ======================================================= -->
      <div style="text-align: center; padding: 36px 16px;">
        <div style="width: 76px; height: 76px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; color: #dc2626; font-size: 2.2rem; border: 2px solid #fca5a5;">
          <i class="fa-solid fa-door-closed"></i>
        </div>
        <h2 style="color: #991b1b; font-size: 1.45rem; font-weight: 700; margin-bottom: 12px; text-transform: uppercase;">Cổng đăng ký nội trú hiện đang đóng</h2>
        <p style="color: #64748b; font-size: 0.98rem; max-width: 620px; margin: 0 auto 24px auto; line-height: 1.6;">
          Hệ thống tiếp nhận hồ sơ lưu trú trực tuyến của Ký túc xá ĐHQG-HCM hiện chưa đến thời gian mở cổng hoặc đã kết thúc đợt đăng ký.
        </p>

        <?php if (!empty($upcomingDot)): ?>
          <div style="max-width: 600px; margin: 0 auto 26px auto; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px; text-align: left; box-shadow: 0 2px 6px rgba(37,99,235,0.06);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
              <span style="background: #2563eb; color: #fff; font-size: 0.75rem; font-weight: 700; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">
                <i class="fa-regular fa-calendar-check" style="margin-right: 4px;"></i> Đợt tiếp theo
              </span>
              <strong style="color: #1e40af; font-size: 1.05rem;"><?= htmlspecialchars($upcomingDot['TenDot']) ?></strong>
            </div>
            <div style="font-size: 0.92rem; color: #1e3a8a; line-height: 1.8;">
              <div>&bull; Năm học: <b><?= htmlspecialchars($upcomingDot['NamHoc']) ?></b></div>
              <div>&bull; Thời gian mở cổng: <b style="color: #2563eb;"><?= date('H:i \n\gà\y d/m/Y', strtotime($upcomingDot['ThoiGianMoCong'])) ?></b></div>
              <div>&bull; Thời gian đóng cổng: <b style="color: #dc2626;"><?= date('H:i \n\gà\y d/m/Y', strtotime($upcomingDot['ThoiGianDongCong'])) ?></b></div>
              <div>&bull; Thời gian giữ chỗ thanh toán: <b><?= (int)$upcomingDot['SoGioGiuCho'] ?> giờ</b> (kể từ khi được duyệt)</div>
            </div>
            <div id="countdownBox" style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed #bfdbfe; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
              <span style="font-size: 0.85rem; color: #1e40af; font-weight: 600;">
                <i class="fa-solid fa-stopwatch" style="margin-right: 4px;"></i> Đếm ngược đến giờ mở cổng:
              </span>
              <span id="countdownTimer" style="font-weight: 700; font-family: monospace; font-size: 1rem; color: #1d4ed8; background: #dbeafe; padding: 4px 12px; border-radius: 6px;">Đang tính...</span>
            </div>
          </div>
          <script>
            (function() {
              const openTime = new Date(<?= json_encode(date('c', strtotime($upcomingDot['ThoiGianMoCong']))) ?>).getTime();
              const timerEl = document.getElementById('countdownTimer');
              function updateCountdown() {
                const now = new Date().getTime();
                const diff = openTime - now;
                if (diff <= 0) {
                  timerEl.textContent = 'ĐÃ ĐẾN GIỜ MỞ CỔNG! Đang làm mới...';
                  setTimeout(() => window.location.reload(), 2000);
                  return;
                }
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const secs = Math.floor((diff % (1000 * 60)) / 1000);
                timerEl.textContent = (days > 0 ? days + ' ngày ' : '') + 
                  String(hours).padStart(2, '0') + 'h ' + 
                  String(mins).padStart(2, '0') + 'm ' + 
                  String(secs).padStart(2, '0') + 's';
              }
              updateCountdown();
              setInterval(updateCountdown, 1000);
            })();
          </script>
        <?php elseif (!empty($latestClosedDot)): ?>
          <div style="max-width: 600px; margin: 0 auto 26px auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; text-align: left;">
            <div style="font-size: 0.92rem; color: #475569; line-height: 1.6;">
              <i class="fa-solid fa-circle-info" style="color: #64748b; margin-right: 6px;"></i>
              Đợt đăng ký gần nhất <b><?= htmlspecialchars($latestClosedDot['TenDot']) ?></b> đã chính thức đóng cổng tiếp nhận vào lúc <b><?= date('H:i \n\gà\y d/m/Y', strtotime($latestClosedDot['ThoiGianDongCong'])) ?></b>. Hiện Ban Quản lý đang thẩm định và bố trí phòng cho các hồ sơ đã nộp.
            </div>
          </div>
        <?php endif; ?>

        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
          <a href="<?= url('login') ?>" class="btn btn-primary" style="border-radius: 8px; padding: 10px 22px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center;">
            <i class="fa-solid fa-right-to-bracket" style="margin-right: 6px;"></i> Đăng nhập hệ thống
          </a>
          <a href="https://ktx.vnuhcm.edu.vn" target="_blank" class="btn btn-outline" style="border-radius: 8px; padding: 10px 22px; font-weight: 600; text-decoration: none; border: 1px solid #cbd5e1; color: #475569; display: inline-flex; align-items: center;">
            <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 6px;"></i> Cổng thông tin KTX
          </a>
        </div>
      </div>

    <?php else: ?>
      <!-- ======================================================= -->
      <!-- GIAO DIỆN KHI CỔNG ĐĂNG KÝ ĐANG MỞ HỢP LỆ               -->
      <!-- ======================================================= -->
      <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <span class="badge" style="background: #16a34a; color: #fff; font-weight: 700; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; display: inline-flex; align-items: center;">
              <i class="fa-solid fa-door-open" style="margin-right: 5px;"></i> Đang mở tiếp nhận
            </span>
            <strong style="color: #166534; font-size: 1.05rem;"><?= htmlspecialchars($activeDot['TenDot']) ?></strong>
          </div>
          <div style="font-size: 0.88rem; color: #15803d; margin-top: 6px; line-height: 1.5;">
            Năm học: <b><?= htmlspecialchars($activeDot['NamHoc']) ?></b> &bull;
            Hạn đóng cổng: <b style="color: #b91c1c;"><?= date('H:i \n\gà\y d/m/Y', strtotime($activeDot['ThoiGianDongCong'])) ?></b> &bull;
            Thời hạn giữ chỗ thanh toán: <b><?= (int)$activeDot['SoGioGiuCho'] ?> giờ</b> kể từ khi được duyệt
          </div>
        </div>
        <div style="font-size: 0.82rem; color: #166534; background: #dcfce7; padding: 6px 14px; border-radius: 8px; border: 1px dashed #86efac; font-weight: 600;">
          <i class="fa-solid fa-circle-check" style="color: #16a34a; margin-right: 4px;"></i> Hệ thống sẵn sàng tiếp nhận
        </div>
      </div>

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
          <div class="form-group"><label>Tòa nhà</label><select id="building" name="building" class="form-control" required><option value="">-- Chọn tòa --</option></select></div>
          <div class="form-group"><label>Tầng</label><select id="floor" name="floor" class="form-control" required><option value="">-- Chọn tầng --</option></select></div>
          <div class="form-group"><label>Phòng</label><select id="room" name="room" class="form-control" required><option value="">-- Chọn phòng --</option></select></div>
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

      <script>
      (function(){
       const optionsUrl=<?= json_encode(url('register/options'), JSON_UNESCAPED_SLASHES) ?>;
       const $=id=>document.getElementById(id);
       const requestVersions={};
       function setHint(message='',isError=false){
        const hint=$('accommodationHint');
        if(!hint) return;
        hint.textContent=message;
        hint.style.color=isError?'#b91c1c':'#64748b';
       }
       function resetSelect(select,placeholder){
        if(!select) return;
        select.innerHTML='';
        select.add(new Option(placeholder,''));
       }
       function setLoading(select,loading){
        if(!select) return;
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
      })();
      </script>
    <?php endif; ?>
  </div>
</div>
<?php
$content=ob_get_clean();
$title='Đăng ký hồ sơ nội trú';
require __DIR__.'/layouts/main.php';
?>