let verificationCode; // Store OTP globally

function sendMail() {
  const emailInput = document.querySelector('input[name="email"]').value.trim();
  const sendButton = document.querySelector('.small-btn');

  sendButton.disabled = true;
  setTimeout(() => sendButton.disabled = false, 30000);

  if (!emailInput) {
    alert("Please enter a valid email address.");
    return;
  }

  verificationCode = Math.floor(100000 + Math.random() * 900000);

  const params = {
    to_email: emailInput,
    passcode: verificationCode,
  };

  const serviceID = "service_7gpd588";
  const templateID = "template_4gadf1w";

  emailjs.send(serviceID, templateID, params)
    .then( res => {
      console.log("✅ Email sent:", res);
      alert("Verification code has been sent to your email!");
    })
    .catch( err  => {
      console.error("❌ Email send failed:", err);
      alert("Failed to send email. Please try again.");
    });
}

function sendCode() {
  const codeInput = document.querySelector('input[name="Code"]').value.trim();

  if (codeInput === verificationCode?.toString()) {
    alert("✅ Verification successful!");
  } else {
    alert("❌ Invalid verification code. Please try again.");
  }
}
