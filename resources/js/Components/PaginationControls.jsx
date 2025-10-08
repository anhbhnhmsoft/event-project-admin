import { ChevronLeft, ChevronRight } from "lucide-react";

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

export default PaginationControls;