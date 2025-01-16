require("dotenv").config();
async function checkDateAndExecute() {
    const today = new Date();
    if (today.getHours() == 21) {
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
setInterval(checkDateAndExecute, 10 * 1000);
