const modal = document.getElementById("profileModal");
const profileDetails = document.getElementById("profileDetails");

function openModal() {
  modal.style.display = "flex";
}

function closeModal() {
  modal.style.display = "none";
}

// FETCH PROFILE DATA
fetch("../php/api/profile.php")
  .then(res => res.json())
  .then(data => {
    if (data.error) {
      profileDetails.innerHTML = "<p>Please login</p>";
      return;
    }

    profileDetails.innerHTML = `
      <p><strong>Name:</strong> ${data.name}</p>
      <p><strong>Email:</strong> ${data.email}</p>
      <p><strong>Phone:</strong> ${data.phone}</p>
      <p><strong>Location:</strong> ${data.location}</p>
      <p><strong>Member Since:</strong> ${data.created_at}</p>
    `;

    document.getElementById("name").value = data.name;
    document.getElementById("phone").value = data.phone;
    document.getElementById("location").value = data.location;
  });

// UPDATE PROFILE
document.getElementById("profileForm").addEventListener("submit", e => {
  e.preventDefault();

  fetch("../php/api/update_profile.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      name: name.value,
      phone: phone.value,
      location: location.value
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      location.reload();
    }
  });
});
