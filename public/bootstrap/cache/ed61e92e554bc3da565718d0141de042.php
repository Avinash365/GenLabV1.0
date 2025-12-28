<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Login</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
</head>
<body>

<h2>Login with OTP</h2>

<input type="text" id="phone" placeholder="+91XXXXXXXXXX">
<button onclick="sendOTP()">Send OTP</button>

<br><br>

<input type="text" id="otp" placeholder="Enter OTP">
<button onclick="verifyOTP()">Verify OTP</button>

<div id="recaptcha-container"></div>

<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import {
  getAuth,
  RecaptchaVerifier,
  signInWithPhoneNumber
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

/**
 * 🔥 IMPORTANT
 * Replace these values with your FIREBASE WEB APP config
 * (NOT android, NOT service account)
 */
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyCHo75IuwSu-2nDybG2J_8L1K8vsrzBiSY",
  authDomain: "genlab-edaa4.firebaseapp.com",
  projectId: "genlab-edaa4",
  storageBucket: "genlab-edaa4.firebasestorage.app",
  messagingSenderId: "828166119765",
  appId: "1:828166119765:web:4ff88707e118f3e1354fdc",
  measurementId: "G-W8M990S88F"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// Setup invisible reCAPTCHA
window.recaptchaVerifier = new RecaptchaVerifier(
  auth,
  'recaptcha-container',
  {
    size: 'invisible',
  }
);

// Send OTP
window.sendOTP = function () {
  const phone = document.getElementById("phone").value;

  if (!phone) {
    alert("Enter phone number");
    return;
  }

  signInWithPhoneNumber(auth, phone, window.recaptchaVerifier)
    .then((confirmationResult) => {
      window.confirmationResult = confirmationResult;
      alert("OTP sent successfully");
    })
    .catch((error) => {
      console.error(error);
      alert(error.message);
    });
};

// Verify OTP
window.verifyOTP = function () {
  const otp = document.getElementById("otp").value;

  if (!otp) {
    alert("Enter OTP");
    return;
  }

  window.confirmationResult.confirm(otp)
    .then(async (result) => {
      const token = await result.user.getIdToken();

      // Send Firebase token to Laravel backend
      fetch("/verify-otp", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
        },
        body: JSON.stringify({ token }),
      })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        if (data.success) {
          window.location.href = "/dashboard";
        }
      });
    })
    .catch(() => alert("Invalid OTP"));
};
</script>

</body>
</html>
<?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/auth/otp-login.blade.php ENDPATH**/ ?>