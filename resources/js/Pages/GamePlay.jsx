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
        playerTitle: "Người chơi đã check-in",
        giftTitle: "Giải thưởng",
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
        playerTitle: "Checked-in Players",
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
                    console.log(res.data);
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

export default function GamePlay() {
    const { props } = usePage();
    const { game, csrf_token } = props;
    const { t } = useTranslation("vi");

    const {
        gifts,
        users,
        history,
        loading,
        meta,
        fetchGifts,
        fetchUsers,
        fetchHistory,
    } = useGameData(game.id, csrf_token);

    // Game Logic State
    const [selectedGift, setSelectedGift] = useState(null);
    const [wheelItems, setWheelItems] = useState([]);
    const [mustSpin, setMustSpin] = useState(false);
    const [disabledSpin, setDisabledSpin] = useState(false);
    const [prizeNumber, setPrizeNumber] = useState(null);
    const [result, setResult] = useState(null);
    const [currentSpinId, setCurrentSpinId] = useState(null);

    const initiateSpin = async () => {
        setDisabledSpin(true);
        if (!selectedGift) {
            alert("Vui lòng chọn phần thưởng trước!");
            return;
        }

        if (mustSpin) return;

        setResult(null);
        setWheelItems([]); // Clear previous items
        setPrizeNumber(null); // Reset prize number to prevent out-of-bounds error with new items
        try {
            const { data } = await axios.post(
                `/event-game/initiate-spin-user/${game.id}`,
                { gift_id: selectedGift.id },
                { headers: { "X-CSRF-TOKEN": csrf_token } }
            );

            if (!data.status) {
                alert(data.message || t("spinFailed"));
                return;
            }

            const { user_id, user, gift, wheel_items } = data.data;

            // Setup Wheel Items from Candidates
            // Setup Wheel Items from Candidates
            const items = wheel_items.map((u) => ({
                option: u.option || u.name,
                style: u.style || {
                    backgroundColor:
                        COLORS[Math.floor(Math.random() * COLORS.length)],
                    textColor: "white",
                },
                image: u.image, // Avatar object properly formatted by backend
            }));

            setWheelItems(items);

            // Find winner index
            const winnerIndex = wheel_items.findIndex(
                (w) => String(w.id) === String(user_id)
            );
            console.log("Wheel items:", wheel_items);
            console.log("User ID:", user_id);
            console.log("Winner index:", winnerIndex);

            if (winnerIndex === -1) {
                console.error("Winner not found in candidates");
                alert("Lỗi dữ liệu vòng quay");
                return;
            }
            setPrizeNumber(winnerIndex);

            // Store the result for display after animation
            setCurrentSpinId({ user, gift });

            // Delay spinning slightly to allow component to mount with new data
            setTimeout(() => {
                setMustSpin(true);
                setDisabledSpin(false);
            }, 100);
        } catch (err) {
            console.error("Initiate spin error:", err);
            alert(err.response.data.message || t("spinError"));
        }
    };

    const handleStopSpinning = async () => {
        if (!currentSpinId) return;

        try {
            // Result is already determined, just display it
            setResult(currentSpinId);
            fetchGifts();
            fetchHistory();
            fetchUsers(); // Refresh user list to update gift status
        } catch (err) {
            console.error("Display result error:", err);
        } finally {
            setMustSpin(false);
            setCurrentSpinId(null);
        }
    };

    const handleCloseResult = () => {
        setResult(null);
        setSelectedGift(null);
        setWheelItems([]);
        setPrizeNumber(null);
        setMustSpin(false);
        fetchGifts(); // Ensure gift quantity is updated
        fetchUsers(); // Ensure user status is updated
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
                        <p className="text-3xl text-gray-800 font-bold from-purple-600 to-pink-600 bg-clip-text">
                            {game.name}
                        </p>
                    </div>
                </div>

                <div className="grid lg:grid-cols-12 gap-6">
                    {/* Left Panel: Prizes */}
                    <div className="lg:col-span-3 bg-white rounded-2xl shadow-xl p-6">
                        <div className="flex items-center gap-2 mb-4">
                            <Gift className="text-pink-500" size={24} />
                            <h2 className="text-xl font-bold text-gray-800">
                                {t("giftTitle")}
                            </h2>
                        </div>
                        <div className="space-y-3 max-h-[600px] overflow-y-auto">
                            {gifts.map((gift) => (
                                <div
                                    key={gift.id}
                                    onClick={() =>
                                        !mustSpin &&
                                        gift.quantity > 0 &&
                                        setSelectedGift(gift)
                                    }
                                    className={`rounded-xl p-4 border-2 transition-all cursor-pointer ${
                                        selectedGift?.id === gift.id
                                            ? "bg-purple-50 border-purple-500 shadow-md transform scale-105"
                                            : "bg-white border-gray-100 hover:border-pink-300"
                                    } ${
                                        mustSpin || gift.quantity <= 0
                                            ? "opacity-60 cursor-not-allowed"
                                            : ""
                                    }`}
                                >
                                    <div className="flex items-start gap-3">
                                        {gift.image && (
                                            <img
                                                src={`/document/${gift.image}`}
                                                alt={gift.name}
                                                className="w-16 h-16 rounded-lg object-cover flex-shrink-0"
                                            />
                                        )}
                                        <div className="flex-1 min-w-0">
                                            <h3 className="font-bold text-gray-800 mb-1">
                                                {gift.name}
                                            </h3>
                                            <div className="flex items-center justify-between">
                                                <span
                                                    className={`text-xs font-medium px-2 py-1 rounded-full ${
                                                        gift.quantity > 0
                                                            ? "bg-green-100 text-green-700"
                                                            : "bg-red-100 text-red-700"
                                                    }`}
                                                >
                                                    {t("quantity", {
                                                        qty: gift.quantity,
                                                    })}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Center Panel: Wheel */}
                    <div className="lg:col-span-6 bg-white rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center relative min-h-[600px]">
                        {!selectedGift && !result && (
                            <div className="absolute inset-0 flex items-center justify-center bg-white z-10 rounded-2xl opacity-90">
                                <p className="text-2xl text-gray-400 font-bold animate-pulse">
                                    ⬅️ Chọn giải thưởng để bắt đầu
                                </p>
                            </div>
                        )}

                        {selectedGift && (
                            <div className="mb-4 text-center animate-fade-in">
                                <p className="text-gray-500 mb-1">
                                    Đang quay giải thưởng:
                                </p>
                                <h3 className="text-2xl font-bold text-purple-600">
                                    {selectedGift.name}
                                </h3>
                            </div>
                        )}

                        <div className="mb-6 relative">
                            {/* Show Wheel only when gift selected AND wheel data ready */}
                            {selectedGift &&
                            wheelItems.length > 0 &&
                            prizeNumber !== null ? (
                                <Wheel
                                    key={`wheel-${
                                        currentSpinId?.user?.id || Date.now()
                                    }`}
                                    mustStartSpinning={mustSpin}
                                    prizeNumber={prizeNumber}
                                    data={wheelItems}
                                    onStopSpinning={handleStopSpinning}
                                    backgroundColors={COLORS}
                                    textColors={["#ffffff"]}
                                    outerBorderColor="#333"
                                    outerBorderWidth={3}
                                    innerBorderColor="#f0f0f0"
                                    radiusLineColor="#fff"
                                    radiusLineWidth={1}
                                    fontSize={14}
                                    perpendicularText={true}
                                    textDistance={60}
                                />
                            ) : (
                                selectedGift && (
                                    <div className="w-[300px] h-[300px] rounded-full border-4 border-dashed border-gray-200 flex items-center justify-center bg-gray-50">
                                        <p className="text-gray-400 font-medium">
                                            Đang chuẩn bị...
                                        </p>
                                    </div>
                                )
                            )}
                        </div>

                        <button
                            onClick={initiateSpin}
                            disabled={
                                !selectedGift ||
                                mustSpin ||
                                disabledSpin ||
                                selectedGift.quantity <= 0
                            }
                            className={`px-12 py-4 rounded-xl font-bold text-xl transition-all transform duration-200 ${
                                mustSpin ||
                                !selectedGift ||
                                selectedGift.quantity <= 0
                                    ? "bg-gray-300 text-gray-500 cursor-not-allowed"
                                    : "bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white shadow-lg hover:shadow-xl hover:scale-105 active:scale-95"
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

                        {result && (
                            <div
                                className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-in fade-in zoom-in duration-300"
                                onClick={() => setResult(null)}
                            >
                                <div
                                    className="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl relative overflow-hidden text-center"
                                    onClick={(e) => e.stopPropagation()}
                                >
                                    <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-purple-500 via-pink-500 to-yellow-500"></div>
                                    <Trophy
                                        size={64}
                                        className="mx-auto text-yellow-500 mb-4 animate-bounce"
                                    />

                                    <h3 className="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600 mb-2">
                                        {t("congratsTitle")}
                                    </h3>

                                    <div className="my-6 p-4 bg-purple-50 rounded-2xl border border-purple-100">
                                        <div className="mb-4">
                                            <p className="text-sm text-gray-500 uppercase tracking-wider mb-1">
                                                Người trúng giải
                                            </p>
                                            {result.user.avatar_path ? (
                                                <img
                                                    src={`/document/${result.user.avatar_path}`}
                                                    className="w-20 h-20 rounded-full mx-auto border-4 border-white shadow-md object-cover mb-2"
                                                />
                                            ) : (
                                                <div className="w-20 h-20 rounded-full bg-purple-200 mx-auto flex items-center justify-center text-purple-700 font-bold text-2xl mb-2">
                                                    {result.user.name.charAt(0)}
                                                </div>
                                            )}
                                            <p className="text-2xl font-bold text-gray-800">
                                                {result.user.name}
                                            </p>
                                        </div>

                                        <div className="border-t border-purple-100 pt-4">
                                            <p className="text-sm text-gray-500 uppercase tracking-wider mb-1">
                                                Giải thưởng
                                            </p>
                                            <p className="text-xl font-bold text-pink-600">
                                                {result.gift.name}
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        onClick={handleCloseResult}
                                        className="w-full py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition-colors"
                                    >
                                        Đóng
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Right Panel: Checked-in Users */}
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
                                <div className="space-y-2 max-h-[600px] overflow-y-auto">
                                    {users.map((user) => (
                                        <div
                                            key={user.id}
                                            className={`w-full text-left p-3 rounded-xl border flex items-center gap-3 hover:shadow-sm transition-all ${
                                                user.has_received_gift
                                                    ? "bg-gray-100 border-gray-200 opacity-60"
                                                    : "bg-white border-green-200 hover:bg-green-50"
                                            }`}
                                        >
                                            {user.avatar_url ? (
                                                <img
                                                    src={user.avatar_url}
                                                    alt={user.name}
                                                    className="w-10 h-10 rounded-full object-cover"
                                                />
                                            ) : (
                                                <div className="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-cyan-400 flex items-center justify-center text-white font-bold">
                                                    {user.name
                                                        .charAt(0)
                                                        .toUpperCase()}
                                                </div>
                                            )}
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center gap-2">
                                                    <div className="font-semibold truncate text-gray-700">
                                                        {user.name}
                                                    </div>
                                                    {user.has_received_gift && (
                                                        <span className="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium whitespace-nowrap">
                                                            ✓ Đã nhận
                                                        </span>
                                                    )}
                                                </div>
                                                {user.membership && (
                                                    <div className="text-xs mt-1 font-medium flex items-center gap-1 text-blue-600">
                                                        <Award size={12} />{" "}
                                                        {user.membership.name}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
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
                </div>
            </div>
        </div>
    );
}
