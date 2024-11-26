// Login functionality
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const role = document.querySelector('input[name="role"]:checked').value;
  const username = document.getElementById("username").value;

  localStorage.setItem("username", username);
  localStorage.setItem("role", role);

  if (role === "karyawan") {
    window.location.href = "karyawan.html";
  } else if (role === "pemilik") {
    window.location.href = "pemilik.html";
  }
});

// Forgot Password functionality
document.getElementById("forgotPasswordLink").addEventListener("click", function (e) {
  e.preventDefault();
  document.getElementById("forgotPasswordModal").style.display = "flex";
});

function closeForgotPasswordModal() {
  document.getElementById("forgotPasswordModal").style.display = "none";
}

document.getElementById("resetPasswordForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const email = document.getElementById("email").value;
  const newPassword = document.getElementById("newPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  if (newPassword !== confirmPassword) {
    alert("Password baru dan konfirmasi password harus sama!");
    return;
  }

  alert(`Password untuk ${email} berhasil direset!`);

  // Reset input fields
  document.getElementById("email").value = "";
  document.getElementById("newPassword").value = "";
  document.getElementById("confirmPassword").value = "";

  closeForgotPasswordModal();
});
