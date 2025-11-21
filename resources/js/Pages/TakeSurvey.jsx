import React, { useState, useEffect } from "react";
import { useForm } from "@inertiajs/react";
import axios from "axios";
// Import các icon từ lucide-react
import {
    CheckCircle,
    XCircle,
    User,
    MessageSquare,
    Send,
    Loader2,
    AlertTriangle,
} from "lucide-react";

export default function TakeSurvey({ poll, user }) {
    const { data, setData, processing } = useForm({
        email: user?.email || "",
        phone: user?.phone || "",
        answers: {},
    });

    // Cập nhật state để quản lý thông báo (message và type: 'success' | 'error')
    const [notification, setNotification] = useState(null); // { message: string, type: 'success' | 'error' }

    const [errors, setErrors] = useState({});
    const [showErrors, setShowErrors] = useState(false);

    /**
     * Hàm hiển thị thông báo tự động ẩn
     * @param {string} message - Nội dung thông báo
     * @param {'success' | 'error'} type - Loại thông báo
     * @param {number} duration - Thời gian hiển thị (ms)
     */
    const showNotification = (message, type, duration = 4000) => {
        setNotification({ message, type });
        setTimeout(() => setNotification(null), duration);
    };

    const handleChange = (questionId, value) => {
        setData("answers", { ...data.answers, [questionId]: value });
        if (errors[`answer_${questionId}`]) {
            const newErrors = { ...errors };
            delete newErrors[`answer_${questionId}`];
            setErrors(newErrors);
        }
    };

    const validateForm = () => {
        const newErrors = {};

        if (!data.email || !data.email.trim()) {
            newErrors.email = "Email là bắt buộc";
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
            newErrors.email = "Email không hợp lệ";
        }

        poll.questions.forEach((question) => {
            const answer = data.answers[question.id.toString()];

            // Kiểm tra câu trả lời
            if (
                !answer ||
                (typeof answer === "string" && !answer.trim()) ||
                (Array.isArray(answer) && answer.length === 0)
            ) {
                newErrors[`answer_${question.id}`] = "Câu hỏi này là bắt buộc";
            }
        });

        return newErrors;
    };

    const scrollToFirstError = () => {
        setTimeout(() => {
            const firstError = document.querySelector(".error-field");
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }
        }, 100);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setShowErrors(true);
        setNotification(null); // Xóa thông báo cũ

        const validationErrors = validateForm();

        if (Object.keys(validationErrors).length > 0) {
            setErrors(validationErrors);
            scrollToFirstError();
            // Bổ sung thông báo lỗi validation tổng quát
            showNotification(
                "Vui lòng kiểm tra lại các trường bị lỗi.",
                "error",
                3000
            );
            return;
        }

        axios
            .post(`/survey/${poll.id}`, data)
            .then((response) => {
                showNotification(
                    "🎉 Gửi khảo sát thành công! Cảm ơn bạn đã tham gia.",
                    "success"
                );
                // Tải lại trang sau khi thông báo thành công
                setTimeout(() => {
                    window.location.reload();
                }, 2500); // Giảm thời gian chờ tải lại
            })
            .catch((error) => {
                let errorMessage =
                    "Đã có lỗi xảy ra khi gửi khảo sát. Vui lòng thử lại sau.";

                if (error.response?.data?.errors) {
                    const responseErrors = {};
                    Object.keys(error.response.data.errors).forEach((key) => {
                        responseErrors[key] =
                            error.response.data.errors[key][0];
                    });
                    setErrors(responseErrors);
                    scrollToFirstError();
                    errorMessage =
                        "Gửi khảo sát thất bại. Vui lòng kiểm tra lại các lỗi chi tiết bên dưới.";
                }

                showNotification(errorMessage, "error");
            });
    };

    const getAnsweredCount = () => {
        return Object.keys(data.answers).filter(
            (key) =>
                data.answers[key] &&
                (typeof data.answers[key] !== "string" ||
                    data.answers[key].trim())
        ).length;
    };

    // Component Thông báo (Toast Notification)
    const NotificationToast = () => {
        if (!notification) return null;

        const { message, type } = notification;

        const isSuccess = type === "success";

        const baseClasses =
            "fixed top-4 left-1/2 transform -translate-x-1/2 z-50 p-4 sm:p-5 rounded-xl text-center font-medium shadow-2xl transition-all duration-500 ease-in-out max-w-sm w-full";
        const successClasses =
            "bg-green-100 text-green-800 border-2 border-green-300";
        const errorClasses =
            "bg-red-100 text-red-800 border-2 border-red-300";

        return (
            <div
                className={`${baseClasses} ${
                    isSuccess ? successClasses : errorClasses
                } animate-fade-in-down`}
                onClick={() => setNotification(null)} // Cho phép click để đóng
                role="alert"
            >
                <div className="flex items-center justify-center gap-3">
                    {isSuccess ? (
                        <CheckCircle className="w-5 h-5 flex-shrink-0" />
                    ) : (
                        <AlertTriangle className="w-5 h-5 flex-shrink-0" />
                    )}
                    <span className="text-sm sm:text-base">{message}</span>
                </div>
            </div>
        );
    };

    // Thêm useEffect để cuộn lên đầu khi có thông báo lỗi mới (chỉ khi có lỗi response từ server)
    useEffect(() => {
        if (Object.keys(errors).length > 0 && notification?.type === "error") {
            scrollToFirstError();
        }
    }, [errors, notification]);

    return (
        <div className="max-w-7xl mx-auto">
            {/* Component Thông báo được đặt ở ngoài cùng để hiển thị cố định */}
            <NotificationToast />

            <div className="text-center mb-6 sm:mb-8 lg:mb-12">
                <h1 className="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900  mb-2 sm:mb-3 px-2">
                    {poll.title}
                </h1>
                {poll.description && (
                    <p className="text-sm sm:text-base text-gray-600 mt-2 px-2">
                        {poll.description}
                    </p>
                )}

                <div className="mt-4 sm:mt-6 bg-white  rounded-xl p-3 sm:p-4 shadow-md inline-block">
                    <div className="flex items-center gap-2 text-sm sm:text-base">
                        <span className="font-semibold text-gray-900 ">
                            {getAnsweredCount()} / {poll.questions.length}
                        </span>
                        <span className="text-gray-600 ">
                            câu đã trả lời
                        </span>
                    </div>
                </div>
            </div>

            {/* Xóa logic thông báo cũ: {successMessage && (...)} */}

            <form onSubmit={handleSubmit} className="space-y-4 sm:space-y-6">
                {/* Card thông tin người dùng */}
                <div className="bg-white rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 border border-gray-200 ">
                    <h2 className="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 mb-4 sm:mb-6 flex items-center">
                        <div className="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
                            {/* Thay thế SVG bằng icon lucide-react */}
                            <User className="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 " />
                        </div>
                        Thông tin của bạn
                    </h2>

                    <div className="grid grid-cols-1 gap-4 sm:gap-5">
                        <div
                            className={
                                errors.email && showErrors ? "error-field" : ""
                            }
                        >
                            <label className="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                                Email <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                className={`w-full border-2 rounded-lg sm:rounded-xl p-3 sm:p-3.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 transition-all text-sm sm:text-base ${
                                    errors.email && showErrors
                                        ? "border-red-500 bg-red-50 "
                                        : "border-gray-300"
                                }`}
                                placeholder="your.email@example.com"
                                value={data.email}
                                onChange={(e) => {
                                    setData("email", e.target.value);
                                    if (errors.email) {
                                        const newErrors = { ...errors };
                                        delete newErrors.email;
                                        setErrors(newErrors);
                                    }
                                }}
                            />
                            {errors.email && showErrors && (
                                <p className="text-red-600  text-xs sm:text-sm mt-2 flex items-center font-medium">
                                    <XCircle className="w-4 h-4 mr-1 flex-shrink-0" />
                                    {errors.email}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                                Số điện thoại
                                <span className="text-gray-400 text-xs sm:text-sm ml-1 font-normal">
                                    (không bắt buộc)
                                </span>
                            </label>
                            <input
                                type="tel"
                                className="w-full border-2 border-gray-300 rounded-lg sm:rounded-xl p-3 sm:p-3.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 transition-all text-sm sm:text-base"
                                placeholder="0123 456 789"
                                value={data.phone}
                                onChange={(e) =>
                                    setData("phone", e.target.value)
                                }
                            />
                        </div>
                    </div>
                </div>

                {/* Card câu hỏi */}
                <div className="bg-white rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 border border-gray-200">
                    <h2 className="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 mb-4 sm:mb-6 flex items-center">
                        <div className="w-8 h-8 sm:w-10 sm:h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
                            {/* Thay thế SVG bằng icon lucide-react */}
                            <MessageSquare className="w-4 h-4 sm:w-5 sm:h-5 text-purple-600 " />
                        </div>
                        Câu hỏi khảo sát
                    </h2>

                    <div className="space-y-5 sm:space-y-6 lg:space-y-8">
                        {poll.questions.map((q, i) => (
                            <div
                                key={q.id}
                                className={`pb-5 sm:pb-6 lg:pb-8 border-b border-gray-200 last:border-b-0 last:pb-0 ${
                                    errors[`answer_${q.id}`] && showErrors
                                        ? "error-field"
                                        : ""
                                }`}
                            >
                                <div className="flex items-start gap-2 sm:gap-3 mb-3 sm:mb-4">
                                    <span className="flex-shrink-0 w-7 h-7 sm:w-9 sm:h-9 bg-gradient-to-br from-blue-500 to-purple-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold shadow-md">
                                        {i + 1}
                                    </span>
                                    <div className="flex-1 min-w-0">
                                        <h3 className="font-semibold text-sm sm:text-base lg:text-lg text-gray-900 break-words">
                                            {q.question}
                                            <span className="text-red-500 ml-1">
                                                *
                                            </span>
                                        </h3>
                                    </div>
                                </div>

                                <div className="pl-9 sm:pl-12">
                                    {q.type === 1 ? (
                                        <div className="space-y-2 sm:space-y-3">
                                            {q.options.map((opt) => (
                                                <label
                                                    key={opt.id}
                                                    className={`flex items-start sm:items-center gap-3 p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 cursor-pointer transition-all group ${
                                                        data.answers[q.id] ===
                                                        opt.id
                                                            ? "border-blue-500 bg-blue-50"
                                                            : errors[
                                                                  `answer_${q.id}`
                                                              ] && showErrors
                                                            ? "border-red-300 hover:border-red-400"
                                                            : "border-gray-200 hover:border-blue-400 hover:bg-blue-50"
                                                    }`}
                                                >
                                                    <input
                                                        type="radio"
                                                        name={`q-${q.id}`}
                                                        value={opt.id}
                                                        checked={
                                                            data.answers[
                                                                q.id.toString()
                                                            ] ===
                                                            opt.id.toString()
                                                        }
                                                        onChange={() =>
                                                            handleChange(
                                                                q.id.toString(),
                                                                opt.id.toString()
                                                            )
                                                        }
                                                        className="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 focus:ring-2 focus:ring-blue-500 flex-shrink-0 mt-0.5 sm:mt-0"
                                                    />
                                                    <span className="text-sm sm:text-base text-gray-700 group-hover:text-gray-900 flex-1 break-words">
                                                        {opt.label}
                                                    </span>
                                                </label>
                                            ))}
                                        </div>
                                    ) : (
                                        <textarea
                                            rows={4}
                                            className={`w-full border-2 rounded-lg sm:rounded-xl p-3 sm:p-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition-all text-sm sm:text-base ${
                                                errors[`answer_${q.id}`] &&
                                                showErrors
                                                    ? "border-red-500 hover:border-red-400 bg-red-50"
                                                    : "border-gray-300 hover:border-gray-400"
                                            }`}
                                            placeholder="Nhập câu trả lời của bạn..."
                                            value={
                                                data.answers[q.id.toString()] ||
                                                ""
                                            }
                                            onChange={(e) =>
                                                handleChange(
                                                    q.id.toString(),
                                                    e.target.value
                                                )
                                            }
                                        />
                                    )}

                                    {errors[`answer_${q.id}`] && showErrors && (
                                        <p className="text-red-600 text-xs sm:text-sm mt-2 flex items-center font-medium">
                                            <XCircle className="w-4 h-4 mr-1 flex-shrink-0" />
                                            {errors[`answer_${q.id}`]}
                                        </p>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Submit button - Fixed on mobile */}
                <div className="sticky bottom-0 left-0 right-0 bg-gradient-to-t from-white via-white to-transparent pt-4 pb-2 sm:pb-4 -mx-3 px-3 sm:mx-0 sm:px-0 sm:static sm:bg-none">
                    <div className="bg-white rounded-xl sm:rounded-2xl shadow-lg sm:shadow-xl p-4 sm:p-6 border border-gray-200">
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold px-6 py-3.5 sm:py-4 rounded-xl shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base active:scale-98"
                        >
                            {processing ? (
                                <>
                                    <Loader2 className="animate-spin h-5 w-5" />
                                    <span>Đang gửi...</span>
                                </>
                            ) : (
                                <>
                                    <Send className="w-5 h-5" />
                                    <span>Gửi khảo sát</span>
                                </>
                            )}
                        </button>

                        <p className="text-xs sm:text-sm text-center text-gray-500  mt-3 sm:mt-4">
                            Vui lòng kiểm tra kỹ thông tin trước khi gửi
                        </p>
                    </div>
                </div>
            </form>
        </div>
    );
}
