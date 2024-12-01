const userRoles = {
  "bram": "pemilik",
  "anggra": "karyawan"
};

// Login functionality
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const username = document.getElementById("username").value.trim().toLowerCase();
  const password = document.getElementById("password").value;

  // Check role based on username
  if (userRoles[username]) {
    const role = userRoles[username];

    // Save login details to localStorage
    localStorage.setItem("username", username);
    localStorage.setItem("role", role);

    // Redirect based on role
    if (role === "pemilik") {
      window.location.href = "pemilik.html"; // Redirect to owner's dashboard
    } else if (role === "karyawan") {
      window.location.href = "karyawan.html"; // Redirect to employee's dashboard
    }
  } else {
    alert("Username atau password salah!");
  }
});

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
})