require("dotenv").config();
const cron = require("node-cron");

cron.schedule("0 1 * * *", async () => {
    try {
        const response = await fetch(
            `${process.env.BASE_URL}/autoclaimingvoucher`
        );
        const response1 = await fetch(`${process.env.BASE_URL}/schedule`);
        const responseJson = await response.json();
        const response1Json = await response1.json();
        console.log(responseJson);
        console.log(response1Json);
    } catch (error) {
        console.log(error);
    }
});
