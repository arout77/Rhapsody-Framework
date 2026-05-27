import React, { useState, useEffect, useRef } from 'react';

export default function BibleSearch() {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [isOpen, setIsOpen] = useState(false);
  
  const searchContainerRef = useRef(null);
  const resultsPerPage = 5;

  // Handle clicking outside the element to automatically close the dropdown
  useEffect(() => {
    function handleClickOutside(event) {
      if (searchContainerRef.current && !searchContainerRef.current.contains(event.target)) {
        setIsOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);

  const executeSearch = async (searchQuery) => {
    if (searchQuery.trim().length < 3) {
      setResults([]);
      setIsOpen(false);
      return;
    }

    setLoading(true);
    setCurrentPage(1);
    setIsOpen(true);

    try {
      const response = await fetch(`/faith/bible/search?q=${encodeURIComponent(searchQuery)}`);
      const data = await response.json();
      setResults(data);
    } catch (err) {
      console.error("Scripture search lookup connection failed:", err);
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (e) => {
    const val = e.target.value;
    setQuery(val);
    if (val.trim().length >= 3) {
      executeSearch(val);
    } else {
      setResults([]);
      setIsOpen(false);
    }
  };

  const handleFormSubmit = (e) => {
    e.preventDefault();
    executeSearch(query);
  };

  const clearAndCloseSearch = () => {
    setQuery('');
    setResults([]);
    setIsOpen(false);
  };

  const indexOfLastResult = currentPage * resultsPerPage;
  const indexOfFirstResult = indexOfLastResult - resultsPerPage;
  const currentResults = results.slice(indexOfFirstResult, indexOfLastResult);
  const totalPages = Math.ceil(results.length / resultsPerPage);

  const highlightText = (text, highlight) => {
    if (!highlight.trim()) return text;

    const musicEscaped = highlight.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const parts = text.split(new RegExp(`(${musicEscaped})`, 'gi'));

    return (
      <span>
        {parts.map((part, i) =>
          part.toLowerCase() === highlight.toLowerCase() ? (
            <mark key={i} className="rounded-sm bg-amber-200 px-0.5 text-slate-950">{part}</mark>
          ) : (
            part
          )
        )}
      </span>
    );
  };

  return (
    <div ref={searchContainerRef} className="scripture-search relative w-full max-w-sm">
      <form onSubmit={handleFormSubmit} className="relative flex items-center">
        <input
          type="text"
          className="w-48 rounded-full border border-white/15 bg-white/10 pl-4 pr-9 py-2 text-sm text-white placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-300/60 lg:w-64"
          placeholder="Search Scripture..."
          value={query}
          onChange={handleInputChange}
          onFocus={() => { if (results.length > 0) setIsOpen(true); }}
        />
        
        {/* Close Button UI Trigger Overlay */}
        {query.length > 0 && (
          <button
            type="button"
            onClick={clearAndCloseSearch}
            className="absolute right-3 p-0.5 rounded-full text-slate-400 hover:text-white hover:bg-white/10 transition-colors focus:outline-none"
            title="Clear and close search"
          >
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
              <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        )}
      </form>

      {loading && (
        <div className="absolute top-full mt-2 w-full rounded-lg border border-slate-700 bg-gray-900 p-3 text-xs font-medium text-slate-400 shadow-xl z-50">
          Searching verses...
        </div>
      )}

      {isOpen && results.length > 0 && (
        <div className="absolute right-0 top-full z-50 mt-2 flex w-[min(24rem,calc(100vw-2rem))] flex-col overflow-hidden rounded-lg border border-slate-700 bg-gray-900 shadow-2xl">
          <div className="max-h-64 overflow-y-auto border-b border-slate-800/60">
            {currentResults.map((v, i) => (
              <div key={`${v.book_name}-${v.chapter}-${v.verse}-${i}`} className="border-b border-slate-800/40 p-3 last:border-0 hover:bg-slate-800/50 transition-colors">
                <div className="mb-1 text-xs font-bold text-sky-400">
                  {v.book_name} {v.chapter}:{v.verse}
                </div>
                <div className="text-xs leading-relaxed text-slate-300">
                  {highlightText(v.text, query)}
                </div>
              </div>
            ))}
          </div>

          {totalPages > 1 && (
            <div className="flex items-center justify-between bg-slate-950/40 p-2 text-[10px] font-mono font-medium text-slate-400">
              <button
                type="button"
                disabled={currentPage === 1}
                onClick={() => setCurrentPage((prev) => prev - 1)}
                className="rounded border border-slate-700 bg-slate-900 px-2 py-1 text-slate-200 hover:bg-slate-800 disabled:opacity-30 disabled:hover:bg-slate-900 transition-colors cursor-pointer"
              >
                Prev
              </button>

              <span>Page {currentPage} of {totalPages}</span>

              <button
                type="button"
                disabled={currentPage === totalPages}
                onClick={() => setCurrentPage((prev) => prev + 1)}
                className="rounded border border-slate-700 bg-slate-900 px-2 py-1 text-slate-200 hover:bg-slate-800 disabled:opacity-30 disabled:hover:bg-slate-900 transition-colors cursor-pointer"
              >
                Next
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  );
}