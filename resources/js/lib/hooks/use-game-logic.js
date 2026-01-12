import axios from "axios";
import { useState } from "react";

/**
 * Constants for wheel colors
 */
const WHEEL_COLORS = [
    "#FF6B6B",
    "#4ECDC4",
    "#FFD93D",
    "#6BCF7F",
    "#A78BFA",
    "#FB923C",
];


const useGameLogic = (game, csrfToken, wheelItems, setWheelItems, t) => {
    const [selectedUser, setSelectedUser] = useState(null);
    const [mustSpin, setMustSpin] = useState(false);
    const [prizeNumber, setPrizeNumber] = useState(null);
    const [result, setResult] = useState(null);
    const [currentSpinId, setCurrentSpinId] = useState(null);

    const initiateSpin = async () => {
        if (!selectedUser) {
            alert(t("selectUser"));
            return;
        }

        if (mustSpin) {
            return;
        }

        setResult(null);

        try {
            const { data } = await axios.post(
                `/event-game/initiate-spin/${game.id}`,
                { user_id: selectedUser.id },
                { headers: { "X-CSRF-TOKEN": csrfToken } }
            );
            console.log('Initiate Spin Data:', data);

            const { spin_id, gift_id, gift } = data.data;
            setCurrentSpinId(spin_id);

            let index = wheelItems.findIndex(
                (w) => String(w.gift.id) === String(gift_id)
            );

            if (index === -1) {
                if (gift) {
                    const newItem = {
                        option: gift.name,
                        style: {
                            backgroundColor:
                                WHEEL_COLORS[
                                wheelItems.length % WHEEL_COLORS.length
                                ],
                            textColor: "white",
                        },
                        gift: gift,
                    };

                    setWheelItems((prev) => [...prev, newItem]);
                    index = wheelItems.length;
                } else {
                    alert(t("giftGone", { gift: "Unknown" }));
                    return;
                }
            }
            setPrizeNumber(index);
            setMustSpin(true);
        } catch (err) {
            console.error(err.response.data.message);
            alert(err.response.data.message);
        }
    };
    const revealPrize = async (onSuccess) => {
        if (!currentSpinId || !selectedUser) {
            return;
        }

        try {
            const { data } = await axios.post(
                `/event-game/reveal-prize/${game.id}`,
                { user_id: selectedUser.id, spin_id: currentSpinId },
                { headers: { "X-CSRF-TOKEN": csrfToken } }
            );

            if (data.status && data.data?.gift) {
                setResult({ user: selectedUser, gift: data.data.gift });
                onSuccess();
            } else {
                alert(data.message || t("revealFailed"));
            }
        } catch (err) {
            console.error("Reveal prize error:", err);
            alert(t("revealFailed"));
        } finally {
            setMustSpin(false);
            setCurrentSpinId(null);
            setTimeout(() => setSelectedUser(null), 1000);
        }
    };

    return {
        selectedUser,
        setSelectedUser,
        mustSpin,
        prizeNumber,
        result,
        initiateSpin,
        revealPrize,
    };
};

export default useGameLogic;
