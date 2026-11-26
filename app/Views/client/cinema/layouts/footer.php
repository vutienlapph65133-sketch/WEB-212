  </main>

  <!-- Footer DARK MODE -->
  <footer class="bg-gray-900 border-t border-gray-800 mt-16 print:hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-4 gap-10">
      <!-- Col 1: Logo & intro -->
      <div class="md:col-span-1">
        <a href="/cinema" class="flex items-center gap-2 text-xl font-bold mb-4">
          <i data-lucide="film" class="w-6 h-6 text-amber-400"></i>
          <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">
            MovieGo
          </span>
        </a>
        <p class="text-gray-400 text-xs leading-relaxed">
          Hệ thống rạp chiếu phim hiện đại hàng đầu Việt Nam. Trải nghiệm điện ảnh đỉnh cao với âm thanh Dolby Atmos và màn hình IMAX Laser sắc nét.
        </p>
      </div>

      <!-- Col 2: Quick Links -->
      <div>
        <h4 class="text-white text-sm font-semibold mb-3">Khám phá</h4>
        <ul class="space-y-2 text-xs text-gray-400">
          <li><a href="/cinema#now-showing" class="hover:text-amber-400 transition-colors">Phim đang chiếu</a></li>
          <li><a href="/cinema#coming-soon" class="hover:text-amber-400 transition-colors">Phim sắp chiếu</a></li>
          <li><a href="/cinema/promotions" class="hover:text-amber-400 transition-colors">Khuyến mãi thành viên</a></li>
          <li><a href="/cinema/ticket" class="hover:text-amber-400 transition-colors">Tra cứu vé điện tử</a></li>
        </ul>
      </div>

      <!-- Col 3: Customer Care -->
      <div>
        <h4 class="text-white text-sm font-semibold mb-3">Hỗ trợ khách hàng</h4>
        <ul class="space-y-2 text-xs text-gray-400">
          <li><button type="button" onclick="openPolicyModal('booking')" class="hover:text-amber-400 transition-colors text-left">Quy định đặt vé online</button></li>
          <li><button type="button" onclick="openPolicyModal('privacy')" class="hover:text-amber-400 transition-colors text-left">Chính sách bảo mật</button></li>
          <li><button type="button" onclick="openPolicyModal('faq')" class="hover:text-amber-400 transition-colors text-left">Câu hỏi thường gặp (FAQ)</button></li>
          <?php if (function_exists('isAdmin') && isAdmin()): ?>
          <li><a href="/admin/cinema" class="hover:text-amber-400 transition-colors">Cổng quản trị nội bộ</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Col 4: Contact & Social -->
      <div>
        <h4 class="text-white text-sm font-semibold mb-3">Liên hệ MovieGo</h4>
        <ul class="space-y-2.5 text-xs text-gray-400">
          <li class="flex items-start gap-2">
            <i data-lucide="map-pin" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
            <span>271 Nguyễn Trãi, Quận 1, TP. Hồ Chí Minh</span>
          </li>
          <li class="flex items-center gap-2">
            <i data-lucide="phone" class="w-4 h-4 text-amber-400 shrink-0"></i>
            <span>Hotline: 1900 6017 (8:00 - 22:00)</span>
          </li>
          <li class="flex items-center gap-2">
            <i data-lucide="mail" class="w-4 h-4 text-amber-400 shrink-0"></i>
            <span>support@moviego.vn</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="border-t border-gray-800 text-center py-5 text-xs text-gray-500">
      © <?= date('Y') ?> MovieGo Cinema. Toàn bộ giao diện Frontend được tối ưu hóa cho Mobile & Desktop.
    </div>
  </footer>

  <!-- Policy Info Modal -->
  <div id="policy-modal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 print:hidden">
    <div class="bg-gray-900 border border-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
      <div class="flex items-center justify-between border-b border-gray-800 pb-3">
        <h3 class="text-base font-bold text-white" id="policy-modal-title">Thông tin hỗ trợ</h3>
        <button type="button" onclick="closePolicyModal()" class="p-1 rounded-lg hover:bg-gray-800 text-gray-400">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <div id="policy-modal-body" class="text-xs text-gray-300 leading-relaxed space-y-2"></div>
      <div class="pt-2 text-right">
        <button type="button" onclick="closePolicyModal()" class="px-5 py-2 rounded-xl bg-amber-500 text-gray-950 font-bold text-xs hover:bg-amber-400">Đã hiểu</button>
      </div>
    </div>
  </div>

  <!-- Initialize Lucide Icons -->
  <script>
    const POLICY_CONTENT = {
      booking: {
        title: 'Quy định đặt vé online',
        body: '<p>• Vui lòng có mặt tại rạp trước giờ chiếu ít nhất 15 phút để xuất trình mã QR check-in.</p><p>• Vé đã đặt thành công không hỗ trợ đổi suất tại quầy tự động; nếu cần hủy đơn hoàn điểm vui lòng liên hệ hotline 1900 6017.</p><p>• Tuân thủ giới hạn độ tuổi (P, T13, T16, T18) theo quy định hiện hành.</p>'
      },
      privacy: {
        title: 'Chính sách bảo mật',
        body: '<p>• MovieGo cam kết bảo mật thông tin cá nhân, số điện thoại và email nhận vé của khách hàng.</p><p>• Mật khẩu được mã hóa an toàn và không chia sẻ dữ liệu người dùng cho bên thứ ba.</p>'
      },
      faq: {
        title: 'Câu hỏi thường gặp (FAQ)',
        body: '<p><strong>1. Làm sao tra cứu lại vé?</strong><br>Vào mục <em>Tra cứu vé</em> nhập mã đơn (ORD-...) hoặc số điện thoại, hoặc đăng nhập để xem ở mục <em>Vé của tôi</em>.</p><p><strong>2. Điểm thưởng dùng như thế nào?</strong><br>1 điểm = 1.000₫, áp dụng tối thiểu 10 điểm và tối đa 50% giá trị đơn hàng.</p>'
      }
    };
    function openPolicyModal(key) {
      const item = POLICY_CONTENT[key];
      if (!item) return;
      document.getElementById('policy-modal-title').innerText = item.title;
      document.getElementById('policy-modal-body').innerHTML = item.body;
      document.getElementById('policy-modal').classList.remove('hidden');
    }
    function closePolicyModal() {
      document.getElementById('policy-modal').classList.add('hidden');
    }
    if (window.lucide) {
      lucide.createIcons();
    }
  </script>
</body>
</html>
