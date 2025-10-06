import * as dotenv from 'dotenv';
import {fileURLToPath} from "url";
import path from "path";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
dotenv.config({
    path: path.resolve(__dirname, '../.env')
});

export const nodeConfig = {
    EXPO_ACCESS_TOKEN: process.env.EXPO_ACCESS_TOKEN,
    APP_KEY_NODE_SERVER: process.env.APP_KEY_NODE_SERVER,
    PORT_NODE_SERVER: process.env.PORT_NODE_SERVER,
}
