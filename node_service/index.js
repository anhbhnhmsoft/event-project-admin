import express from 'express';
import {nodeConfig} from "./config.js";
import sendNotification from "./services/notification.js";

const app = express();

const middlewareCheckApiKey = (req, res, next) => {
    const apiKey = req.headers['x-api-key-node'];
    const requiredKey = nodeConfig.APP_KEY_NODE_SERVER;
    if (apiKey && apiKey === requiredKey) {
        next();
    } else {
        return res.status(401).json({
            status: false,
            message: "INVALID API KEY!!"
        });
    }
}

// Middleware: Bắt buộc để đọc dữ liệu JSON được gửi từ Laravel (hoặc bất kỳ client nào)
app.use(express.json());


app.post('/send-notification', middlewareCheckApiKey, async (req, res) => {
    const {common_payload, batch} = req.body;
    if (!common_payload || !batch || batch.length === 0) {
        return res.status(400).json({
            status: false,
            error: 'Missing common_payload or messages array.'
        });
    }
    const {
        messages,
        status,
        error_notifications,
        success_notifications
    } = await sendNotification(common_payload, batch);

    return res.status(200).json({
        status,
        error: messages,
        error_notifications,
        success_notifications
    });
})

app.listen(nodeConfig.PORT_NODE_SERVER, () => {
    console.log(`🚀 Node.js Notification Service running at http://localhost:${nodeConfig.PORT_NODE_SERVER}`);
});
