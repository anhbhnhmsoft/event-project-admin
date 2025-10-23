import React, { useState } from "react";
import { useForm } from "@inertiajs/react";
import axios from "axios";

export default function TakeSurvey({ poll, user }) {
    const { data, setData, processing } = useForm({
        email: user?.email || "",
        phone: user?.phone || "",
        answers: {},
    });

    const [errors, setErrors] = useState({});
    const [showErrors, setShowErrors] = useState(false);

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
            const answer = data.answers[question.id];

            if (!answer || (typeof answer === "string" && !answer.trim())) {
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

        const validationErrors = validateForm();

        if (Object.keys(validationErrors).length > 0) {
            setErrors(validationErrors);
            scrollToFirstError();
            return;
        }

        axios
            .post(`/survey/${poll.id}`, data)
            .then((response) => {
                alert("Cảm ơn bạn đã hoàn thành khảo sát!");
            })
            .catch((error) => {
                if (error.response?.data?.errors) {
                    const responseErrors = {};
                    Object.keys(error.response.data.errors).forEach((key) => {
                        responseErrors[key] =
                            error.response.data.errors[key][0];
                    });
                    setErrors(responseErrors);
                    scrollToFirstError();
                } else {
                    alert(
                        "Đã có lỗi xảy ra khi gửi khảo sát. Vui lòng thử lại sau."
                    );
                }
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

    return (
        <div className="max-w-7xl mx-auto">
            <div className="text-center mb-6 sm:mb-8 lg:mb-12">
                <h1 className="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-2 sm:mb-3 px-2">
                    {poll.title}
                </h1>
                {poll.description && (
                    <p className="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-2 px-2">
                        {poll.description}
                    </p>
                )}

                <div className="mt-4 sm:mt-6 bg-white dark:bg-gray-800 rounded-xl p-3 sm:p-4 shadow-md inline-block">
                    <div className="flex items-center gap-2 text-sm sm:text-base">
                        <span className="font-semibold text-gray-900 dark:text-white">
                            {getAnsweredCount()} / {poll.questions.length}
                        </span>
                        <span className="text-gray-600 dark:text-gray-400">
                            câu đã trả lời
                        </span>
                    </div>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 sm:space-y-6">
                {/* Card thông tin người dùng */}
                <div className="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 border border-gray-200 dark:border-gray-700">
                    <h2 className="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 dark:text-white mb-4 sm:mb-6 flex items-center">
                        <div className="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
                            <svg
                                className="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 dark:text-blue-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                        </div>
                        Thông tin của bạn
                    </h2>

                    <div className="grid grid-cols-1 gap-4 sm:gap-5">
                        <div
                            className={
                                errors.email && showErrors ? "error-field" : ""
                            }
                        >
                            <label className="block text-sm sm:text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Email <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                className={`w-full border-2 rounded-lg sm:rounded-xl p-3 sm:p-3.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all text-sm sm:text-base ${
                                    errors.email && showErrors
                                        ? "border-red-500 dark:border-red-500 bg-red-50 dark:bg-red-900/20"
                                        : "border-gray-300 dark:border-gray-600"
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
                                <p className="text-red-600 dark:text-red-400 text-xs sm:text-sm mt-2 flex items-center font-medium">
                                    <svg
                                        className="w-4 h-4 mr-1 flex-shrink-0"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fillRule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                    {errors.email}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm sm:text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Số điện thoại
                                <span className="text-gray-400 text-xs sm:text-sm ml-1 font-normal">
                                    (không bắt buộc)
                                </span>
                            </label>
                            <input
                                type="tel"
                                className="w-full border-2 border-gray-300 dark:border-gray-600 rounded-lg sm:rounded-xl p-3 sm:p-3.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all text-sm sm:text-base"
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
                <div className="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 border border-gray-200 dark:border-gray-700">
                    <h2 className="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 dark:text-white mb-4 sm:mb-6 flex items-center">
                        <div className="w-8 h-8 sm:w-10 sm:h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
                            <svg
                                className="w-4 h-4 sm:w-5 sm:h-5 text-purple-600 dark:text-purple-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        Câu hỏi khảo sát
                    </h2>

                    <div className="space-y-5 sm:space-y-6 lg:space-y-8">
                        {poll.questions.map((q, i) => (
                            <div
                                key={q.id}
                                className={`pb-5 sm:pb-6 lg:pb-8 border-b border-gray-200 dark:border-gray-700 last:border-b-0 last:pb-0 ${
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
                                        <h3 className="font-semibold text-sm sm:text-base lg:text-lg text-gray-900 dark:text-white break-words">
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
                                                            ? "border-blue-500 bg-blue-50 dark:bg-blue-900/20"
                                                            : errors[
                                                                  `answer_${q.id}`
                                                              ] && showErrors
                                                            ? "border-red-300 dark:border-red-700 hover:border-red-400"
                                                            : "border-gray-200 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-700"
                                                    }`}
                                                >
                                                    <input
                                                        type="radio"
                                                        name={`q-${q.id}`}
                                                        value={opt.id}
                                                        checked={
                                                            data.answers[
                                                                q.id
                                                            ] === opt.id
                                                        }
                                                        onChange={() =>
                                                            handleChange(
                                                                q.id,
                                                                opt.id
                                                            )
                                                        }
                                                        className="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 focus:ring-2 focus:ring-blue-500 flex-shrink-0 mt-0.5 sm:mt-0"
                                                    />
                                                    <span className="text-sm sm:text-base text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white flex-1 break-words">
                                                        {opt.label}
                                                    </span>
                                                </label>
                                            ))}
                                        </div>
                                    ) : (
                                        <textarea
                                            rows={4}
                                            className={`w-full border-2 rounded-lg sm:rounded-xl p-3 sm:p-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white resize-none transition-all text-sm sm:text-base ${
                                                errors[`answer_${q.id}`] &&
                                                showErrors
                                                    ? "border-red-500 dark:border-red-500 bg-red-50 dark:bg-red-900/20"
                                                    : "border-gray-300 dark:border-gray-600"
                                            }`}
                                            placeholder="Nhập câu trả lời của bạn..."
                                            value={data.answers[q.id] || ""}
                                            onChange={(e) =>
                                                handleChange(
                                                    q.id,
                                                    e.target.value
                                                )
                                            }
                                        />
                                    )}

                                    {errors[`answer_${q.id}`] && showErrors && (
                                        <p className="text-red-600 dark:text-red-400 text-xs sm:text-sm mt-2 flex items-center font-medium">
                                            <svg
                                                className="w-4 h-4 mr-1 flex-shrink-0"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fillRule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                    clipRule="evenodd"
                                                />
                                            </svg>
                                            {errors[`answer_${q.id}`]}
                                        </p>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Submit button - Fixed on mobile */}
                <div className="sticky bottom-0 left-0 right-0 bg-gradient-to-t from-white via-white to-transparent dark:from-gray-900 dark:via-gray-900 pt-4 pb-2 sm:pb-4 -mx-3 px-3 sm:mx-0 sm:px-0 sm:static sm:bg-none">
                    <div className="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-lg sm:shadow-xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700">
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold px-6 py-3.5 sm:py-4 rounded-xl shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base active:scale-98"
                        >
                            {processing ? (
                                <>
                                    <svg
                                        className="animate-spin h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            className="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            strokeWidth="4"
                                        ></circle>
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    <span>Đang gửi...</span>
                                </>
                            ) : (
                                <>
                                    <svg
                                        className="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                                        />
                                    </svg>
                                    <span>Gửi khảo sát</span>
                                </>
                            )}
                        </button>

                        <p className="text-xs sm:text-sm text-center text-gray-500 dark:text-gray-400 mt-3 sm:mt-4">
                            Vui lòng kiểm tra kỹ thông tin trước khi gửi
                        </p>
                    </div>
                </div>
            </form>
        </div>
    );
}
