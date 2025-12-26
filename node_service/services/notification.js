import  {Expo} from "expo-server-sdk";
export default async function sendNotification(common_payload, batch){
    const expo = new Expo();
    const expoMessages = [];
    // Mảng ánh xạ để lưu trữ mối quan hệ giữa index của expoMessages và notification_id
    const messageInfo = [];
    const errorNotifications = [];
    const successNotifications = [];

    for (const message of batch) {
        for (const token of message.tokens) {
            // Kiểm tra xem có dạng này ko
            if (!Expo.isExpoPushToken(token)){
                errorNotifications.push(message.notification_id)
            }else{
                expoMessages.push({
                    to: token,
                    sound: 'default',
                    title: common_payload.title,
                    body: common_payload.description,
                    data: {
                        notification_id: message.notification_id,
                        notification_type: common_payload.notification_type,
                        data: common_payload.data ? common_payload.data : {}
                    },
                });
                messageInfo.push(message.notification_id);
            }
        }
    }
    if (expoMessages.length === 0){
        return {
            status: false,
            messages: "Không có token nào phù hợp",
            error_notifications: errorNotifications,
            success_notifications: successNotifications
        }
    }
    // push message thành các chunk
    const chunks = expo.chunkPushNotifications(expoMessages);
    let messageIndex = 0; // Biến theo dõi vị trí trong mảng expoMessages
    try {
        for (let chunk of chunks) {
            // Lấy thông tin ID tương ứng với chunk hiện tại
            const currentChunkInfo = messageInfo.slice(messageIndex, messageIndex + chunk.length);
            let ticketChunks = await expo.sendPushNotificationsAsync(chunk);
            for (let i = 0; i < ticketChunks.length; i++){
                const ticket = ticketChunks[i];
                const notificationId = currentChunkInfo[i];
                if (ticket.status === "ok"){
                    // Đẩy ID bản ghi Laravel vào mảng thành công
                    successNotifications.push(notificationId);
                }else{
                    // Đẩy ID bản ghi Laravel vào mảng thất bại
                    errorNotifications.push(notificationId);
                }
            }
            // Cập nhật index cho lô tiếp theo
            messageIndex += ticketChunks.length;
        }
        return {
            status: true,
            messages: "Gửi thông báo hoàn tất.",
            error_notifications: errorNotifications,
            success_notifications: successNotifications
        };
    }catch (e){
        return {
            status: false,
            messages: `Có lỗi xẩy ra khi gửi notifications: ${e.message || 'Internal Server Error'}`,
            error_notifications: errorNotifications,
            success_notifications: successNotifications
        }
    }
}


