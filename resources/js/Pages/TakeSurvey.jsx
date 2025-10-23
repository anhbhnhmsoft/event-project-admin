import React from "react";
import { useForm } from "@inertiajs/react";
import axios from "axios";
export default function TakeSurvey({ poll, user }) {
    const { data, setData, post, processing, errors } = useForm({
        email: user?.email || "",
        phone: user?.phone || "",
        answers: {},
    });

    const handleChange = (questionId, value) => {
        setData("answers", { ...data.answers, [questionId]: value });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        axios
            .post(`/survey/${poll.id}`, data)
            .then((response) => {
                alert("Cảm ơn bạn đã hoàn thành khảo sát!");
                window.location.reload();
            })
            .catch((error) => {
                if (error.response && error.response.data) {
                    const responseErrors = error.response.data.errors;
                    for (const key in responseErrors) {
                        if (responseErrors.hasOwnProperty(key)) {
                            errors[key] = responseErrors[key][0];
                        }
                    }
                } else {
                    alert(
                        "Đã có lỗi xảy ra khi gửi khảo sát. Vui lòng thử lại sau."
                    );
                }
            });
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 py-6 px-4 sm:py-12 sm:px-6 lg:px-8">
            <div className="max-w-4xl mx-auto">
                {/* Header */}
                <div className="text-center mb-8 sm:mb-12">
                    <h1 className="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                        {poll.title}
                    </h1>
                    {poll.description && (
                        <p className="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-2">
                            {poll.description}
                        </p>
                    )}
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Card thông tin người dùng */}
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 lg:p-8 border border-gray-200 dark:border-gray-700">
                        <h2 className="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-4 sm:mb-6 flex items-center">
                            <svg
                                className="w-5 h-5 sm:w-6 sm:h-6 mr-2 text-blue-600"
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
                            Thông tin của bạn
                        </h2>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div className="md:col-span-2 lg:col-span-1">
                                <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Email{" "}
                                    <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    className="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all text-sm sm:text-base"
                                    placeholder="your.email@example.com"
                                    required
                                    value={data.email}
                                    onChange={(e) =>
                                        setData("email", e.target.value)
                                    }
                                />
                                {errors.email && (
                                    <p className="text-red-500 text-xs sm:text-sm mt-1 flex items-center">
                                        <svg
                                            className="w-4 h-4 mr-1"
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

                            <div className="md:col-span-2 lg:col-span-1">
                                <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Số điện thoại
                                    <span className="text-gray-400 text-xs ml-1 font-normal">
                                        (không bắt buộc)
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    className="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all text-sm sm:text-base"
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
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 lg:p-8 border border-gray-200 dark:border-gray-700">
                        <h2 className="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-4 sm:mb-6 flex items-center">
                            <svg
                                className="w-5 h-5 sm:w-6 sm:h-6 mr-2 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                />
                            </svg>
                            Khảo sát
                        </h2>

                        <div className="space-y-6 sm:space-y-8">
                            {poll.questions.map((q, i) => (
                                <div
                                    key={q.id}
                                    className="pb-6 sm:pb-8 border-b border-gray-200 dark:border-gray-700 last:border-b-0 last:pb-0"
                                >
                                    <div className="flex items-start mb-3 sm:mb-4">
                                        <span className="flex-shrink-0 w-6 h-6 sm:w-8 sm:h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mr-3">
                                            {i + 1}
                                        </span>
                                        <h3 className="font-medium text-base sm:text-lg text-gray-900 dark:text-white flex-1">
                                            {q.question}
                                            {q.required && (
                                                <span className="text-red-500 ml-1">
                                                    *
                                                </span>
                                            )}
                                        </h3>
                                    </div>

                                    <div className="ml-9 sm:ml-11">
                                        {q.type === 1 ? (
                                            <div className="space-y-2 sm:space-y-3">
                                                {q.options.map((opt) => (
                                                    <label
                                                        key={opt.id}
                                                        className="flex items-center gap-3 p-3 sm:p-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 cursor-pointer transition-all hover:bg-blue-50 dark:hover:bg-gray-700 group"
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
                                                            className="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                                        />
                                                        <span className="text-sm sm:text-base text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white">
                                                            {opt.label}
                                                        </span>
                                                    </label>
                                                ))}
                                            </div>
                                        ) : (
                                            <textarea
                                                rows={4}
                                                className="w-full border-2 border-gray-300 dark:border-gray-600 rounded-lg p-3 sm:p-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white resize-none transition-all text-sm sm:text-base"
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
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Submit button */}
                    <div className="flex flex-col sm:flex-row gap-3 sm:gap-4 items-stretch sm:items-center justify-between bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 border border-gray-200 dark:border-gray-700">
                        <p className="text-xs sm:text-sm text-gray-600 dark:text-gray-400 text-center sm:text-left">
                            <span className="text-red-500">*</span> Các trường
                            bắt buộc
                        </p>
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold px-6 sm:px-8 py-3 sm:py-3.5 rounded-lg shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base"
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
                                    Đang gửi...
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
                                    Gửi khảo sát
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
