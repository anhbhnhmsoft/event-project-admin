import { useState, useEffect, useMemo } from "react";
import { Wheel } from "react-custom-roulette";
import { Gift, Users, Trophy, Sparkles, Award, ChevronLeft, ChevronRight } from "lucide-react";
import { usePage } from "@inertiajs/react";

export default function GamePlay() {
    const { props } = usePage();
    const { game, users: initialUsers } = props;

    const [selectedUser, setSelectedUser] = useState(null);
    const [mustSpin, setMustSpin] = useState(false);
    const [prizeNumber, setPrizeNumber] = useState(0);
    const [result, setResult] = useState(null);
    const [history, setHistory] = useState([]);
    const [gifts, setGifts] = useState([]);
    const [users, setUsers] = useState(initialUsers);

    const [historyMeta, setHistoryMeta] = useState({
        current_page: 1,
        last_page: 1,
        total: 0
    });
    const [usersMeta, setUsersMeta] = useState({
        current_page: initialUsers.meta?.current_page || 1,
        last_page: initialUsers.meta?.last_page || 1,
        total: initialUsers.meta?.total || initialUsers.data.length
    });
    const [loadingHistory, setLoadingHistory] = useState(false);
    const [loadingUsers, setLoadingUsers] = useState(false);

    const COLORS = ["#FF6B6B", "#4ECDC4", "#FFD93D", "#6BCF7F", "#A78BFA", "#FB923C"];

    const wheelData = useMemo(
        () =>
            gifts
                .filter((gift) => gift.quantity > 0)
                .map((gift, index) => ({
                option: gift.name,
                style: {
                    backgroundColor: COLORS[index % COLORS.length],
                    textColor: "white",
                },
                gift,
            })),
        [gifts]
    );

    const fetchHistory = async (page = 1) => {
        setLoadingHistory(true);
        try {
            const res = await fetch(`/api/event-game/history-gifts/${game.id}?per_page=10&page=${page}`, {
                headers: { Accept: "application/json" },
            });
            if (!res.ok) throw new Error("Failed to fetch history");

            const data = await res.json();
            if (data.status) {
                setHistory(Array.isArray(data.data) ? data.data : []);
                setHistoryMeta(data.meta || { current_page: 1, last_page: 1, total: 0 });
            }
        } catch (error) {
            console.error("Error fetching history:", error);
        } finally {
            setLoadingHistory(false);
        }
    };

    const fetchUsers = async (page = 1) => {
        setLoadingUsers(true);
        try {
            const res = await fetch(`/api/event-game/users/${game.id}?per_page=20&page=${page}`, {
                headers: { Accept: "application/json" },
            });
            if (!res.ok) throw new Error("Failed to fetch users");

            const data = await res.json();
            if (data.status) {
                setUsers({ data: data.data });
                setUsersMeta(data.meta || { current_page: 1, last_page: 1, total: 0 });
            }
        } catch (error) {
            console.error("Error fetching users:", error);
        } finally {
            setLoadingUsers(false);
        }
    };

    const fetchGifts = async () => {
        try {
            const res = await fetch(`/api/event-game/gifts/${game.id}`, {
                headers: { Accept: "application/json" },
            });
            if (!res.ok) throw new Error("Failed to fetch gifts");

            const data = await res.json();
            if (data.status) setGifts(data.data);
        } catch (error) {
            console.error("Error fetching gifts:", error);
        }
    };

    useEffect(() => {
        fetchGifts();
        fetchHistory();
    }, []);

    const calculatePrize = () => {
        const validGifts = gifts.filter((g) => g.quantity > 0 && g.rate > 0);

        const totalRate = validGifts.reduce((sum, g) => sum + g.rate, 0);

        const random = Math.random() * totalRate;
        let cumulative = 0;

        for (let g of validGifts) {
            cumulative += g.rate;
            if (random <= cumulative) {
                return gifts.findIndex((gift) => gift.id === g.id);
            }
        }

        return gifts.findIndex((gift) => gift.id === validGifts[validGifts.length - 1].id);
    };


    const handleSpin = () => {
        if (!selectedUser) {
            alert("Vui lòng chọn người chơi!");
            return;
        }
        if (mustSpin) return;

        const prize = calculatePrize();
        if (prize === null) return;

        setPrizeNumber(prize);
        setMustSpin(true);
        setResult(null);
    };

    const handleStopSpinning = async () => {
        setMustSpin(false);
        const winningGift = gifts[prizeNumber];

        const newResult = {
            user: selectedUser,
            gift: winningGift,
            timestamp: new Date().toISOString(),
        };

        setResult(newResult);

        try {
            const response = await fetch(`/api/event-game/history-gifts/${game.id}`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    user_id: selectedUser.id,
                    event_game_gift_id: winningGift.id,
                }),
            });

            const data = await response.json();

            if (response.ok && data.status) {
                fetchHistory(1);
                fetchGifts();
            } else {
                alert(data.message || 'Có lỗi xảy ra khi lưu kết quả');
            }
        } catch (err) {
            console.error("Insert history failed", err);
            alert('Không thể lưu kết quả, vui lòng thử lại');
        }
    };

    const formatTime = (timeStr) => {
        try {
            return new Date(timeStr).toLocaleTimeString("vi-VN", {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        } catch {
            return timeStr;
        }
    };

    const PaginationControls = ({ meta, onPageChange, loading }) => (
        <div className="flex items-center justify-between mt-4 text-sm">
            <span className="text-gray-600">
                Trang {meta.current_page} / {meta.last_page}
            </span>
            <div className="flex gap-2">
                <button
                    onClick={() => onPageChange(meta.current_page - 1)}
                    disabled={meta.current_page === 1 || loading}
                    className="px-3 py-1 bg-gray-200 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-300 transition-colors flex items-center gap-1"
                >
                    <ChevronLeft size={16} />
                    Trước
                </button>
                <button
                    onClick={() => onPageChange(meta.current_page + 1)}
                    disabled={meta.current_page === meta.last_page || loading}
                    className="px-3 py-1 bg-gray-200 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-300 transition-colors flex items-center gap-1"
                >
                    Sau
                    <ChevronRight size={16} />
                </button>
            </div>
        </div>
    );

    return (
        <div className="min-h-screen bg-gradient-to-br from-purple-100 via-pink-50 to-blue-100 p-6">
            <div className="max-w-7xl mx-auto">
                <div className="bg-white rounded-2xl shadow-xl p-6 mb-6">
                    <div className="flex items-center gap-3 mb-2">
                        <Sparkles className="text-yellow-500" size={32} />
                        <h1 className="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                            {game.name}
                        </h1>
                    </div>
                    <p className="text-gray-600 text-lg">{game.description}</p>
                </div>

                <div className="grid lg:grid-cols-12 gap-6">
                    <div className="lg:col-span-3 bg-white rounded-2xl shadow-xl p-6">
                        <div className="flex items-center gap-2 mb-4">
                            <Users className="text-blue-500" size={24} />
                            <h2 className="text-xl font-bold text-gray-800">Người chơi</h2>
                            <span className="ml-auto bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
                                {usersMeta.total}
                            </span>
                        </div>

                        {loadingUsers ? (
                            <div className="text-center py-8 text-gray-500">Đang tải...</div>
                        ) : (
                            <>
                                <div className="space-y-2 max-h-[500px] overflow-y-auto">
                                    {users.data.map((user) => (
                                        <button
                                            key={user.id}
                                            onClick={() => !mustSpin && setSelectedUser(user)}
                                            disabled={mustSpin}
                                            className={`w-full text-left p-3 rounded-xl transition-all ${selectedUser?.id === user.id
                                                ? "bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg"
                                                : "bg-gray-50 hover:bg-gray-100 border-2 border-gray-200 hover:border-purple-300"
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
                                                        {user.name.charAt(0).toUpperCase()}
                                                    </div>
                                                )}
                                                <div className="flex-1 min-w-0">
                                                    <div className="font-semibold truncate">{user.name}</div>
                                                    {user.membership && (
                                                        <div className="text-xs mt-1 font-medium flex items-center gap-1">
                                                            <Award size={12} />
                                                            {user.membership.name}
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </button>
                                    ))}
                                </div>

                                <PaginationControls
                                    meta={usersMeta}
                                    onPageChange={fetchUsers}
                                    loading={loadingUsers}
                                />
                            </>
                        )}
                    </div>

                    <div className="lg:col-span-6 bg-white rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center">
                        <div className="mb-6">
                            {wheelData.length > 0 ? (
                                <Wheel
                                    mustStartSpinning={mustSpin}
                                    prizeNumber={prizeNumber}
                                    data={wheelData}
                                    onStopSpinning={handleStopSpinning}
                                    backgroundColors={COLORS}
                                    textColors={["#ffffff"]}
                                    outerBorderColor="#333"
                                    outerBorderWidth={5}
                                    innerBorderColor="#f0f0f0"
                                    radiusLineColor="#ffffff"
                                    radiusLineWidth={2}
                                    fontSize={16}
                                    perpendicularText={false}
                                    textDistance={60}
                                />
                            ) : (
                                <div className="p-6 text-center text-sm text-gray-500">Đang tải vòng quay...</div>
                            )}
                        </div>

                        <button
                            onClick={handleSpin}
                            disabled={!selectedUser || mustSpin}
                            className={`px-12 py-4 rounded-xl font-bold text-xl transition-all transform ${mustSpin
                                ? "bg-gray-300 cursor-not-allowed"
                                : selectedUser
                                    ? "bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white shadow-lg hover:shadow-2xl hover:scale-105 active:scale-95"
                                    : "bg-gray-200 text-gray-500 cursor-not-allowed"
                                }`}
                        >
                            {mustSpin ? "🎰 Đang quay..." : "🎯 QUAY NGAY!"}
                        </button>

                        {selectedUser && !result && (
                            <div className="mt-6 text-center">
                                <p className="text-gray-600">Người chơi:</p>
                                <p className="text-xl font-bold text-purple-600">{selectedUser.name}</p>
                            </div>
                        )}

                        {result && (
                            <div className="mt-8 bg-gradient-to-r from-yellow-400 via-yellow-500 to-orange-500 rounded-2xl p-6 w-full max-w-md shadow-2xl animate-bounce">
                                <div className="text-center text-white">
                                    <Trophy size={48} className="mx-auto mb-3" />
                                    <h3 className="text-2xl font-bold mb-2">🎉 CHÚC MỪNG! 🎉</h3>
                                    <p className="text-lg mb-1">{result.user.name}</p>
                                    <p className="text-xl font-bold">Đã trúng: {result.gift.name}</p>
                                    {result.gift.description && (
                                        <p className="text-sm mt-2 opacity-90">{result.gift.description}</p>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="lg:col-span-3 space-y-6">
                        <div className="bg-white rounded-2xl shadow-xl p-6">
                            <div className="flex items-center gap-2 mb-4">
                                <Gift className="text-pink-500" size={24} />
                                <h2 className="text-xl font-bold text-gray-800">Phần quà</h2>
                            </div>
                            <div className="space-y-3 max-h-[400px] overflow-y-auto">
                                {gifts.map((gift) => (
                                    <div
                                        key={gift.id}
                                        className="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border-2 border-gray-200"
                                    >
                                        <div className="flex items-center gap-3">
                                            {gift.image && (
                                                <img
                                                    src={`/image/${gift.image}`}
                                                    alt={gift.name}
                                                    className="w-12 h-12 rounded-lg object-cover"
                                                />
                                            )}
                                            <div className="flex-1">
                                                <h3 className="font-bold text-gray-800">{gift.name}</h3>
                                                <p className="text-xs text-gray-500 mt-1">{gift.description}</p>
                                                <p className="text-xs text-gray-700 mt-1">Số lượng: {gift.quantity}</p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-xl p-6">
                            <div className="flex items-center gap-2 mb-4">
                                <Trophy className="text-yellow-500" size={24} />
                                <h2 className="text-xl font-bold text-gray-800">Lịch sử</h2>
                            </div>

                            {loadingHistory ? (
                                <div className="text-center py-8 text-gray-500">Đang tải...</div>
                            ) : (
                                <>
                                    {history.length > 0 ? (
                                        <>
                                            <div className="space-y-2 max-h-80 overflow-y-auto">
                                                {history.map((item, index) => (
                                                    <div
                                                        key={`${item.id}-${index}`}
                                                        className="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-3 border border-purple-200"
                                                    >
                                                        <div className="flex items-center justify-between">
                                                            <div>
                                                                <p className="font-semibold text-sm text-gray-800">
                                                                    {item.user.name}
                                                                </p>
                                                                <p className="text-xs text-purple-600">→ {item.gift.name}</p>
                                                            </div>
                                                            <span className="text-xs text-gray-500">
                                                                {formatTime(item.created_at)}
                                                            </span>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>

                                            <PaginationControls
                                                meta={historyMeta}
                                                onPageChange={fetchHistory}
                                                loading={loadingHistory}
                                            />
                                        </>
                                    ) : (
                                        <p className="text-center text-gray-500 py-4">Chưa có lịch sử</p>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}