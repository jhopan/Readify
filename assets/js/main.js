// Main JavaScript

// Flash Message
document.addEventListener("DOMContentLoaded", function () {
  // Auto hide flash messages after 5 seconds
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0";
      setTimeout(() => alert.remove(), 300);
    }, 5000);
  });
});

// Modal Functions
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add("active");
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove("active");
  }
}

// Close modal when clicking outside
document.addEventListener("click", function (e) {
  if (e.target.classList.contains("modal")) {
    e.target.classList.remove("active");
  }
});

// Confirm Delete
function confirmDelete(message) {
  return confirm(message || "Apakah Anda yakin ingin menghapus data ini?");
}

// Format Rupiah
function formatRupiah(amount) {
  return "Rp " + parseInt(amount).toLocaleString("id-ID");
}

// Format Date
function formatDate(dateString) {
  const options = { year: "numeric", month: "long", day: "numeric" };
  return new Date(dateString).toLocaleDateString("id-ID", options);
}

// Search with debounce
let searchTimeout;
function searchData(callback, delay = 500) {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(callback, delay);
}

// Toggle sidebar on mobile
function toggleSidebar() {
  const sidebar = document.querySelector(".sidebar");
  sidebar.classList.toggle("active");
}
