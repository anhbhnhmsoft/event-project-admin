import { useState, useEffect, useCallback, useMemo } from "react";
import { Wheel } from "react-custom-roulette";
import { Gift, Users, Trophy, Sparkles, Award, Loader2 } from "lucide-react";
import { usePage } from "@inertiajs/react";
import PaginationControls from "../Components/PaginationControls";
import axios from "axios";

// --- Translation System ---
const translations = {
    vi: {
        selectUser: "Vui lòng chọn người chơi!",
        spinError: "Không thể quay, vui lòng thử lại!",
        spinStartTitle: "🎰 Đang quay...",
        spinStartBtn: "🎯 QUAY NGAY!",
        playerTitle: "Người chơi",
        giftTitle: "Phần quà",
        historyTitle: "Lịch sử quay",
        congratsTitle: "🎉 CHÚC MỪNG! 🎉",
        wonMessage: "Đã trúng: {gift}",
        loading: "Đang tải...",
        loadingWheel: "Đang tải vòng quay...",
        noHistory: "Chưa có lịch sử",
        quantity: "🎁 Số lượng: {qty}",
        playerLabel: "Người chơi:",
        giftGone: 'Phần quà "{gift}" không còn trên vòng quay (hoặc ẩn).',
        spinFailed: "Không thể quay thưởng.",
        revealFailed: "Không thể nhận giải thưởng.",
    },
    en: {
        selectUser: "Please select a player!",
        spinError: "Cannot spin, please try again!",
        spinStartTitle: "🎰 Spinning...",
        spinStartBtn: "🎯 SPIN NOW!",
        playerTitle: "Players",
        giftTitle: "Prizes",
        historyTitle: "History",
        congratsTitle: "🎉 CONGRATULATIONS! 🎉",
        wonMessage: "Won: {gift}",
        loading: "Loading...",
        loadingWheel: "Loading wheel...",
        noHistory: "No history yet",
        quantity: "🎁 Qty: {qty}",
        playerLabel: "Player:",
        giftGone: 'Prize "{gift}" is no longer available.',
        spinFailed: "Spin failed.",
        revealFailed: "Cannot reveal prize.",
    },
};

const COLORS = [
    "#FF6B6B",
    "#4ECDC4",
    "#FFD93D",
    "#6BCF7F",
    "#A78BFA",
    "#FB923C",
];

const useTranslation = (locale = "vi") => {
    const t = useCallback(
        (key, params = {}) => {
            let text =
                translations[locale]?.[key] || translations["vi"][key] || key;
            Object.keys(params).forEach((param) => {
                text = text.replace(`{${param}}`, params[param]);
            });
            return text;
        },
        [locale]
    );
    return { t };
};

// --- Custom Hooks ---

// Hook for fetching initial data
const useGameData = (gameId, csrfToken) => {
    const [gifts, setGifts] = useState([]);
    const [wheelItems, setWheelItems] = useState([]);
    const [users, setUsers] = useState([]);
    const [history, setHistory] = useState([]);
    const [loading, setLoading] = useState({
        users: false,
        history: false,
        gifts: false,
    });
    const [meta, setMeta] = useState({
        users: { current_page: 1, last_page: 1, total: 0 },
        history: { current_page: 1, last_page: 1, total: 0 },
    });

    const fetchGifts = useCallback(async () => {
        try {
            const res = await axios.get(`/event-game/gifts/${gameId}`, {
                headers: { "X-CSRF-TOKEN": csrfToken },
            });
            if (res.data?.status) {
                const filtered = (res.data.data || []).filter(
                    (g) => g.quantity > 0
                );
                setGifts(filtered);
                setWheelItems(
                    filtered.map((gift, i) => ({
                        option: gift.name,
                        style: {
                            backgroundColor: COLORS[i % COLORS.length],
                            textColor: "white",
                        },
                        gift,
                    }))
                );
            }
        } catch (e) {
            console.error("Fetch gifts failed", e);
        }
    }, [gameId, csrfToken]);

    const fetchUsers = useCallback(
        async (page = 1) => {
            setLoading((prev) => ({ ...prev, users: true }));
            try {
                const res = await axios.get(`/event-game/users/${gameId}`, {
                    params: { page, per_page: 20 },
                    headers: { "X-CSRF-TOKEN": csrfToken },
                });
                if (res.data?.status) {
                    setUsers(res.data.data || []);
                    setMeta((prev) => ({ ...prev, users: res.data.meta }));
                }
            } catch (e) {
                console.error("Fetch users failed", e);
            } finally {
                setLoading((prev) => ({ ...prev, users: false }));
            }
        },
        [gameId, csrfToken]
    );

    const fetchHistory = useCallback(
        async (page = 1) => {
            setLoading((prev) => ({ ...prev, history: true }));
            try {
                const res = await axios.get(
                    `/event-game/history-gifts/${gameId}`,
                    {
                        params: { page, per_page: 10 },
                        headers: { "X-CSRF-TOKEN": csrfToken },
                    }
                );
                if (res.data?.status) {
                    setHistory(res.data.data || []);
                    setMeta((prev) => ({ ...prev, history: res.data.meta }));
                }
            } catch (e) {
                console.error("Fetch history failed", e);
            } finally {
                setLoading((prev) => ({ ...prev, history: false }));
            }
        },
        [gameId, csrfToken]
    );

    useEffect(() => {
        fetchGifts();
        fetchUsers();
        fetchHistory();
    }, [fetchGifts, fetchUsers, fetchHistory]);

    return {
        gifts,
        wheelItems,
        users,
        history,
        loading,
        meta,
        fetchGifts,
        fetchUsers,
        fetchHistory,
        setGifts,
        setWheelItems,
        setHistory,
        setHistoryMeta: (m) => setMeta((p) => ({ ...p, history: m })),
    };
};

// Hook for Game Logic
const useGameLogic = (game, csrfToken, wheelItems, setWheelItems, t) => {
    const [selectedUser, setSelectedUser] = useState(null);
    const [mustSpin, setMustSpin] = useState(false);
    const [prizeNumber, setPrizeNumber] = useState(null);
    const [result, setResult] = useState(null);
    const [currentSpinId, setCurrentSpinId] = useState(null);

    const initiateSpin = async () => {
        if (!selectedUser) return alert(t("selectUser"));
        if (mustSpin) return;

        setResult(null); // Clear previous result
        try {
            const { data } = await axios.post(
                `/event-game/initiate-spin/${game.id}`,
                { user_id: selectedUser.id },
                { headers: { "X-CSRF-TOKEN": csrfToken } }
            );

            if (!data.status || !data.data?.spin_id) {
                return alert(data.message || t("spinFailed"));
            }

            console.log("Initiate Spin Data:", data.data); // Debugging Log

            const { spin_id, gift_id, gift } = data.data; // Now we have full gift object
            setCurrentSpinId(spin_id);

            // Find index of the winning gift
            // Use String comparison to be safe with BigInt IDs
            let index = wheelItems.findIndex(
                (w) => String(w.gift.id) === String(gift_id)
            );

            if (index === -1) {
                // AUTO RECOVERY: If gift not found in wheel items, add it dynamically
                if (gift) {
                    const newItem = {
                        option: gift.name,
                        style: {
                            backgroundColor:
                                COLORS[wheelItems.length % COLORS.length],
                            textColor: "white",
                        },
                        gift: gift,
                    };

                    // Add to wheel items and update index
                    setWheelItems((prev) => {
                        const newItems = [...prev, newItem];
                        return newItems;
                    });

                    index = wheelItems.length;
                } else {
                    // Fallback error if no gift data delivered
                    alert(t("giftGone", { gift: "Unknown" }));
                    return;
                }
            }

            setPrizeNumber(index);
            setMustSpin(true);
        } catch (err) {
            console.error("Initiate spin error:", err);
            alert(t("spinError"));
        }
    };

    const revealPrize = async (onSuccess) => {
        if (!currentSpinId || !selectedUser) return;

        try {
            const { data } = await axios.post(
                `/event-game/reveal-prize/${game.id}`,
                { user_id: selectedUser.id, spin_id: currentSpinId },
                { headers: { "X-CSRF-TOKEN": csrfToken } }
            );

            if (data.status && data.data?.gift) {
                setResult({ user: selectedUser, gift: data.data.gift });
                onSuccess(); // Refresh data
            } else {
                alert(data.message || t("revealFailed"));
            }
        } catch (err) {
            console.error("Reveal prize error:", err);
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

export default function GamePlay() {
    const { props } = usePage();
    const { game, csrf_token } = props;
    const { t } = useTranslation("vi");

    const {
        gifts,
        wheelItems,
        users,
        history,
        loading,
        meta,
        fetchGifts,
        fetchUsers,
        fetchHistory,
        setWheelItems,
    } = useGameData(game.id, csrf_token);

    const {
        selectedUser,
        setSelectedUser,
        mustSpin,
        prizeNumber,
        result,
        initiateSpin,
        revealPrize,
    } = useGameLogic(game, csrf_token, wheelItems, setWheelItems, t);

    const handleStopSpinning = () => {
        revealPrize(() => {
            fetchGifts();
            fetchHistory();
        });
    };

    const formatTime = (timeStr) =>
        new Date(timeStr).toLocaleTimeString("vi-VN", {
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
        });

    return (
        <div className="min-h-screen bg-gradient-to-br from-purple-100 via-pink-50 to-blue-100 p-6">
            <div className="max-w-7xl mx-auto">
                {/* Header */}
                <div className="bg-white rounded-2xl shadow-xl p-6 mb-6">
                    <div className="flex items-center gap-3 py-3 h-12">
                        <Sparkles className="text-yellow-500" size={32} />
                        <p className="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                            {game.name}
                        </p>
                    </div>
                    <p className="text-gray-600 text-lg">{game.description}</p>
                </div>

                <div className="grid lg:grid-cols-12 gap-6">
                    {/* Left Panel: Users */}
                    <div className="lg:col-span-3 bg-white rounded-2xl shadow-xl p-6">
                        <div className="flex items-center gap-2 mb-4">
                            <Users className="text-blue-500" size={24} />
                            <h2 className="text-xl font-bold text-gray-800">
                                {t("playerTitle")}
                            </h2>
                            <span className="ml-auto bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
                                {meta.users.total}
                            </span>
                        </div>

                        {loading.users ? (
                            <div className="flex justify-center py-8">
                                <Loader2 className="animate-spin text-purple-500" />
                            </div>
                        ) : (
                            <>
                                <div className="space-y-2 max-h-[500px] overflow-y-auto">
                                    {users.map((user) => (
                                        <button
                                            key={user.id}
                                            onClick={() =>
                                                !mustSpin &&
                                                setSelectedUser(user)
                                            }
                                            disabled={mustSpin}
                                            className={`w-full text-left p-3 rounded-xl transition-all cursor-pointer ${
                                                selectedUser?.id === user.id
                                                    ? "bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg"
                                                    : "bg-gray-50 hover:bg-gray-100 border-2 border-gray-200 hover:border-purple-300"
                                            } ${
                                                mustSpin
                                                    ? "opacity-50 cursor-not-allowed"
                                                    : ""
                                            }`}
                                        >
                                            <div className="flex items-center gap-3">
                                                {user.avatar_url ? (
                                                    <img
                                                        src={user.avatar_url}
                                                        alt={user.name}
                                                        className="w-10 h-10 rounded-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white font-bold">
                                                        {user.name
                                                            .charAt(0)
                                                            .toUpperCase()}
                                                    </div>
                                                )}
                                                <div className="flex-1 min-w-0">
                                                    <div className="font-semibold truncate">
                                                        {user.name}
                                                    </div>
                                                    {user.membership && (
                                                        <div className="text-xs mt-1 font-medium flex items-center gap-1">
                                                            <Award size={12} />{" "}
                                                            {
                                                                user.membership
                                                                    .name
                                                            }
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </button>
                                    ))}
                                </div>
                                <PaginationControls
                                    meta={meta.users}
                                    onPageChange={fetchUsers}
                                    loading={loading.users}
                                />
                            </>
                        )}
                    </div>

                    {/* Center Panel: Wheel */}
                    <div className="lg:col-span-6 bg-white rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center relative">
                        <div className="mb-6">
                            {wheelItems.length > 0 ? (
                                <Wheel
                                    mustStartSpinning={mustSpin}
                                    prizeNumber={prizeNumber}
                                    data={wheelItems}
                                    onStopSpinning={handleStopSpinning}
                                    backgroundColors={[
                                        "#FF6B6B",
                                        "#4ECDC4",
                                        "#FFD93D",
                                        "#6BCF7F",
                                        "#A78BFA",
                                        "#FB923C",
                                    ]}
                                    textColors={["#fff"]}
                                    outerBorderColor="#333"
                                    outerBorderWidth={5}
                                    innerBorderColor="#f0f0f0"
                                    radiusLineColor="#fff"
                                    radiusLineWidth={2}
                                    fontSize={16}
                                    perpendicularText={false}
                                    textDistance={60}
                                />
                            ) : (
                                <div className="p-6 text-center text-sm text-gray-500">
                                    {t("loadingWheel")}
                                </div>
                            )}
                        </div>

                        <button
                            onClick={initiateSpin}
                            disabled={!selectedUser || mustSpin}
                            className={`px-12 py-4 rounded-xl font-bold text-xl transition-all transform duration-200 ${
                                mustSpin || !selectedUser
                                    ? "bg-gray-300 text-gray-500 cursor-not-allowed"
                                    : "bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white shadow-lg hover:shadow-xl hover:scale-105 active:scale-95"
                            }`}
                        >
                            {mustSpin ? (
                                <span className="flex items-center gap-2">
                                    <Loader2 className="animate-spin" />{" "}
                                    {t("spinStartTitle")}
                                </span>
                            ) : (
                                t("spinStartBtn")
                            )}
                        </button>

                        {selectedUser && !result && (
                            <div className="mt-6 text-center animate-fade-in">
                                <p className="text-gray-600">
                                    {t("playerLabel")}
                                </p>
                                <p className="text-xl font-bold text-purple-600">
                                    {selectedUser.name}
                                </p>
                            </div>
                        )}

                        {result && (
                            <div className="mt-8 bg-gradient-to-r from-yellow-400 via-yellow-500 to-orange-500 rounded-2xl p-6 w-full max-w-md shadow-2xl animate-bounce z-10">
                                <div className="text-center text-white">
                                    <Trophy
                                        size={48}
                                        className="mx-auto mb-3"
                                    />
                                    <h3 className="text-2xl font-bold mb-2">
                                        {t("congratsTitle")}
                                    </h3>
                                    <p className="text-lg mb-1">
                                        {result.user.name}
                                    </p>
                                    <p className="text-xl font-bold">
                                        {t("wonMessage", {
                                            gift: result.gift.name,
                                        })}
                                    </p>
                                    {result.gift.description && (
                                        <p className="text-sm mt-2 opacity-90 text-white">
                                            {result.gift.description}
                                        </p>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Right Panel: Gifts & History */}
                    <div className="lg:col-span-3 space-y-6">
                        {/* Gifts List */}
                        <div className="bg-white rounded-2xl shadow-xl p-6">
                            <div className="flex items-center gap-2 mb-4">
                                <Gift className="text-pink-500" size={24} />
                                <h2 className="text-xl font-bold text-gray-800">
                                    {t("giftTitle")}
                                </h2>
                            </div>
                            <div className="space-y-3 max-h-[400px] overflow-y-auto">
                                {gifts.map((gift) => (
                                    <div
                                        key={gift.id}
                                        className="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border-2 border-gray-200 hover:border-pink-200 transition-colors"
                                    >
                                        <div className="flex items-center gap-3">
                                            {gift.image && (
                                                <img
                                                    src={`/document/${gift.image}`}
                                                    alt={gift.name}
                                                    className="w-12 h-12 rounded-lg object-cover"
                                                />
                                            )}
                                            <div className="flex-1">
                                                <h3 className="font-bold text-gray-800">
                                                    {gift.name}
                                                </h3>
                                                <p className="text-xs text-gray-500 mt-1 line-clamp-2">
                                                    {gift.description}
                                                </p>
                                                <p className="text-xs text-purple-600 font-medium mt-1">
                                                    {t("quantity", {
                                                        qty: gift.quantity,
                                                    })}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* History List */}
                        <div className="bg-white rounded-2xl shadow-xl p-6">
                            <div className="flex items-center gap-2 mb-4">
                                <Trophy className="text-yellow-500" size={24} />
                                <h2 className="text-xl font-bold text-gray-800">
                                    {t("historyTitle")}
                                </h2>
                            </div>

                            {loading.history ? (
                                <div className="flex justify-center py-8">
                                    <Loader2 className="animate-spin text-purple-500" />
                                </div>
                            ) : history.length > 0 ? (
                                <>
                                    <div className="space-y-2 max-h-80 overflow-y-auto">
                                        {history.map((item) => (
                                            <div
                                                key={item.id}
                                                className="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-3 border border-purple-200 hover:shadow-sm transition-shadow"
                                            >
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <p className="font-semibold text-sm text-gray-800">
                                                            {item.user.name}
                                                        </p>
                                                        <p className="text-xs text-purple-600">
                                                            → {item.gift.name}
                                                        </p>
                                                    </div>
                                                    <span className="text-xs text-gray-500">
                                                        {formatTime(
                                                            item.created_at
                                                        )}
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                    <PaginationControls
                                        meta={meta.history}
                                        onPageChange={fetchHistory}
                                        loading={loading.history}
                                    />
                                </>
                            ) : (
                                <p className="text-center text-gray-500 py-4">
                                    {t("noHistory")}
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
