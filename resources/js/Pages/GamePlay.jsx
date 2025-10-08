import { useState, useEffect, useMemo } from "react";
import { Wheel } from "react-custom-roulette";
import { Gift, Users, Trophy, Sparkles, Award } from "lucide-react";
import { usePage } from "@inertiajs/react";
import PaginationControls from "../Components/PaginationControls";

export default function GamePlay() {
    const { props } = usePage();
    const { game, csrf_token } = props;

    const [selectedUser, setSelectedUser] = useState(null);
    const [mustSpin, setMustSpin] = useState(false);
    const [prizeNumber, setPrizeNumber] = useState(null);
    const [result, setResult] = useState(null);
    const [history, setHistory] = useState([]);
    const [gifts, setGifts] = useState([]);
    const [users, setUsers] = useState([]);
    const [wheelItems, setWheelItems] = useState([]);

    const [loadingHistory, setLoadingHistory] = useState(false);
    const [loadingUsers, setLoadingUsers] = useState(false);

    const [usersMeta, setUsersMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [historyMeta, setHistoryMeta] = useState({ current_page: 1, last_page: 1, total: 0 });

    const COLORS = ["#FF6B6B", "#4ECDC4", "#FFD93D", "#6BCF7F", "#A78BFA", "#FB923C"];

    useEffect(() => {
        let isMounted = true;

        const fetchAll = async () => {
            try {
                const giftsRes = await axios.get(`/event-game/gifts/${game.id}`, {
                    headers: { 'X-CSRF-TOKEN': csrf_token },
                });
                if (isMounted && giftsRes.data?.status) {
                    const filtered = (giftsRes.data.data || []).filter(g => g.quantity > 0);
                    setGifts(filtered);

                    const items = filtered.map((gift, i) => ({
                        option: gift.name,
                        style: {
                            backgroundColor: COLORS[i % COLORS.length],
                            textColor: "white",
                        },
                        gift,
                    }));
                    setWheelItems(items);
                }

                setLoadingHistory(true);
                const historyRes = await axios.get(`/event-game/history-gifts/${game.id}`, {
                    params: { page: 1, per_page: 10 },
                    headers: { 'X-CSRF-TOKEN': csrf_token },
                });
                if (isMounted && historyRes.data?.status) {
                    setHistory(historyRes.data.data || []);
                    setHistoryMeta(historyRes.data.meta);
                }

                setLoadingUsers(true);
                const usersRes = await axios.get(`/event-game/users/${game.id}`, {
                    params: { page: 1, per_page: 20 },
                    headers: { 'X-CSRF-TOKEN': csrf_token },
                });
                if (isMounted && usersRes.data?.status) {
                    setUsers(usersRes.data.data || []);
                    setUsersMeta(usersRes.data.meta);
                }
            } catch (e) {
                console.error("Fetch data failed:", e);
            } finally {
                setLoadingUsers(false);
                setLoadingHistory(false);
            }
        };

        fetchAll();
        return () => { isMounted = false };
    }, [game.id, csrf_token]);


    const handleSpin = async () => {
        if (!selectedUser) return alert("Vui lòng chọn người chơi!");
        if (mustSpin) return;

        try {
            const { data } = await axios.post(
                `/event-game/spin/${game.id}`,
                { user_id: selectedUser.id },
                { headers: { 'X-CSRF-TOKEN': csrf_token } }
            );

            if (!data.status || !data.data?.gift) {
                return alert(data.message || "Không thể quay thưởng.");
            }

            const winningGift = data.data.gift;
            const index = wheelItems.findIndex(w => w.gift.id === winningGift.id);

            if (index === -1) {
                alert(`Phần quà "${winningGift.name}" không còn trên vòng quay.`);
                return;
            }

            setPrizeNumber(index);
            setMustSpin(true);
            setResult(null);
        } catch (err) {
            console.error("Spin error:", err);
            alert("Không thể quay, vui lòng thử lại!");
        }
    };

    const handleStopSpinning = async () => {
        setMustSpin(false);
        if (!Number.isInteger(prizeNumber) || prizeNumber < 0 || prizeNumber >= wheelItems.length) return;

        const winningGift = wheelItems[prizeNumber]?.gift;
        const newResult = { user: selectedUser, gift: winningGift, timestamp: new Date().toISOString() };
        setResult(newResult);

        try {
            const [giftsRes, historyRes] = await Promise.all([
                axios.get(`/event-game/gifts/${game.id}`, { headers: { 'X-CSRF-TOKEN': csrf_token } }),
                axios.get(`/event-game/history-gifts/${game.id}`, { params: { page: 1, per_page: 10 }, headers: { 'X-CSRF-TOKEN': csrf_token } }),
            ]);

            if (giftsRes.data?.status) {
                const filtered = (giftsRes.data.data || []).filter(g => g.quantity > 0);
                setGifts(filtered);
                setWheelItems(filtered.map((gift, i) => ({
                    option: gift.name,
                    style: {
                        backgroundColor: COLORS[i % COLORS.length],
                        textColor: "white",
                    },
                    gift,
                })));
            }

            if (historyRes.data?.status) {
                setHistory(historyRes.data.data || []);
                setHistoryMeta(historyRes.data.meta);
            }
        } catch (err) {
            console.error("Update after spin failed:", err);
        }

        setTimeout(() => setSelectedUser(null), 1000);
    };

    const formatTime = (timeStr) =>
        new Date(timeStr).toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit", second: "2-digit" });

    return (
        <div className="min-h-screen bg-gradient-to-br from-purple-100 via-pink-50 to-blue-100 p-6">
            <div className="max-w-7xl mx-auto">
                <div className="bg-white rounded-2xl shadow-xl p-6 mb-6">
                    <div className="flex items-center gap-3 py-3 h-12" >
                        <Sparkles className="text-yellow-500" size={32} />
                        <p className="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent" >
                            {game.name}
                        </p>
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
                                    {users.map((user) => (
                                        <button
                                            key={user.id}
                                            onClick={() => !mustSpin && setSelectedUser(user)}
                                            disabled={mustSpin}
                                            className={`w-full text-left p-3 rounded-xl transition-all cursor-pointer ${selectedUser?.id === user.id
                                                ? "bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg"
                                                : "bg-gray-50 hover:bg-gray-100 border-2 border-gray-200 hover:border-purple-300"
                                                }`}
                                        >
                                            <div className="flex items-center gap-3">
                                                {user.avatar_url ? (
                                                    <img
                                                        src={`/document/${user.avatar_url}`}
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

                                <PaginationControls meta={usersMeta} onPageChange={() => { }} loading={loadingUsers} />
                            </>
                        )}
                    </div>
                    <div className="lg:col-span-6 bg-white rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center">
                        <div className="mb-6">
                            {wheelItems.length > 0
                                ? (
                                    <Wheel
                                        mustStartSpinning={mustSpin}
                                        prizeNumber={prizeNumber}
                                        data={wheelItems}
                                        onStopSpinning={handleStopSpinning}
                                        backgroundColors={COLORS}
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
                                        Đang tải vòng quay...
                                    </div>
                                )}
                        </div>

                        <button
                            onClick={handleSpin}
                            disabled={!selectedUser || mustSpin}
                            className={`px-12 py-4 rounded-xl font-bold text-xl transition-all transform ${mustSpin
                                ? "bg-gray-300 cursor-pointer"
                                : selectedUser
                                    ? "bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white shadow-lg hover:shadow-2xl hover:scale-105 active:scale-95"
                                    : "bg-gray-200 text-gray-500 cursor-pointer"
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
                                    <div key={gift.id} className="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border-2 border-gray-200">
                                        <div className="flex items-center gap-3">
                                            {gift.image && (
                                                <img src={`/document/${gift.image}`} alt={gift.name} className="w-12 h-12 rounded-lg object-cover" />
                                            )}
                                            <div className="flex-1">
                                                <h3 className="font-bold text-gray-800">{gift.name}</h3>
                                                <p className="text-xs text-gray-500 mt-1">{gift.description}</p>
                                                <p className="text-xs text-gray-700 mt-1">🎁 Số lượng: {gift.quantity}</p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-xl p-6">
                            <div className="flex items-center gap-2 mb-4">
                                <Trophy className="text-yellow-500" size={24} />
                                <h2 className="text-xl font-bold text-gray-800">Lịch sử quay</h2>
                            </div>

                            {loadingHistory ? (
                                <div className="text-center py-8 text-gray-500">Đang tải...</div>
                            ) : history.length > 0 ? (
                                <>
                                    <div className="space-y-2 max-h-80 overflow-y-auto">
                                        {history.map((item) => (
                                            <div
                                                key={item.id}
                                                className="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-3 border border-purple-200"
                                            >
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <p className="font-semibold text-sm text-gray-800">{item.user.name}</p>
                                                        <p className="text-xs text-purple-600">→ {item.gift.name}</p>
                                                    </div>
                                                    <span className="text-xs text-gray-500">
                                                        {formatTime(item.created_at)}
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    <PaginationControls meta={historyMeta} onPageChange={() => { }} loading={loadingHistory} />
                                </>
                            ) : (
                                <p className="text-center text-gray-500 py-4">Chưa có lịch sử</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
