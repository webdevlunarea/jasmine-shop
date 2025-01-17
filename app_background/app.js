require("dotenv").config();
async function checkDateAndExecute() {
    const today = new Date();
    if (today.getHours() == 1) {
        const response = await fetch(
            `${process.env.BASE_URL}autoclaimingvoucher`,
            {
                method: "POST",
                headers: { "Content-Type": "application/json" },
            }
        );
        const responseJson = await response.json();
        console.log(responseJson);
    }
}

// Jalankan fungsi setiap jam
setInterval(checkDateAndExecute, 60 * 60 * 1000);
