import React, { useEffect, useState } from 'react';

export default function BibleTrivia() {
  const triviaEndpoint = '/faith/api/trivia/questions';
  const [questions, setQuestions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [currentIdx, setCurrentIdx] = useState(0);
  const [selectedOption, setSelectedOption] = useState(null);
  const [isAnswered, setIsAnswered] = useState(false);
  const [score, setScore] = useState(0);
  const [quizComplete, setQuizComplete] = useState(false);

  const loadQuestions = () => {
    setLoading(true);

    fetch(triviaEndpoint)
      .then((res) => res.json())
      .then((resData) => {
        if (resData.success) {
          setQuestions(resData.data);
        }
        setLoading(false);
      })
      .catch((err) => {
        console.error('Error loading trivia:', err);
        setLoading(false);
      });
  };

  useEffect(() => {
    loadQuestions();
  }, []);

  if (loading) {
    return <div className="text-center p-6 text-slate-600">Loading trivia challenge...</div>;
  }

  if (questions.length === 0) {
    return <div className="text-center p-6 text-slate-600">No questions available right now.</div>;
  }

  const currentQuestion = questions[currentIdx];

  const handleOptionClick = (optionKey) => {
    if (isAnswered) return;

    setSelectedOption(optionKey);
    setIsAnswered(true);

    if (optionKey?.toUpperCase() === currentQuestion.correct_option?.toUpperCase()) {
      setScore((prev) => prev + 1);
    }
  };

  const handleNext = () => {
    setSelectedOption(null);
    setIsAnswered(false);

    if (currentIdx + 1 < questions.length) {
      setCurrentIdx((prev) => prev + 1);
    } else {
      setQuizComplete(true);
    }
  };

  const resetQuiz = () => {
    setQuizComplete(false);
    setCurrentIdx(0);
    setSelectedOption(null);
    setIsAnswered(false);
    setScore(0);
    loadQuestions();
  };

  if (quizComplete) {
    return (
      <div className="trivia-card mx-auto max-w-xl p-7 text-center">
        <h2 className="mb-4 text-2xl font-bold text-slate-900">Quiz Complete!</h2>
        <p className="mb-6 text-xl text-slate-700">
          You scored <span className="font-bold text-teal-700">{score}</span> out of{' '}
          <span className="font-semibold">{questions.length}</span>
        </p>
        <button
          onClick={resetQuiz}
          className="rounded-lg bg-teal-700 px-6 py-2 font-medium text-white shadow-sm transition hover:bg-teal-800"
        >
          Play Again
        </button>
      </div>
    );
  }

  return (
    <div className="trivia-card mx-auto max-w-xl p-7">
      <div className="mb-5 flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-slate-500">
        <span>Question {currentIdx + 1} of {questions.length}</span>
        <span>Score: {score}</span>
      </div>

      <h3 className="mb-6 text-xl font-bold leading-snug text-slate-950">
        {currentQuestion.question}
      </h3>

      {/* pointer-events-none after answering instead of disabled, so browser UA styles don't override colours */}
      <div className={`space-y-3 ${isAnswered ? 'pointer-events-none' : ''}`}>
        {['A', 'B', 'C', 'D'].map((key) => {
          const optionText = currentQuestion[`option_${key.toLowerCase()}`];
          const isCorrectOption = key.toUpperCase() === currentQuestion.correct_option?.toUpperCase();
          const isSelectedOption = selectedOption?.toUpperCase() === key.toUpperCase();

          let optionStyles;
          if (!isAnswered) {
            optionStyles = 'w-full rounded-lg bg-gray-100 text-black border px-4 py-3 text-left text-sm font-medium transition-colors duration-200 border-slate-200 text-slate-700 hover:border-teal-200 hover:bg-teal-50';
          } else if (isCorrectOption) {
            optionStyles = 'w-full rounded-lg border px-4 py-3 text-left text-sm font-medium border-emerald-500 bg-emerald-50 text-emerald-800';
          } else if (isSelectedOption) {
            optionStyles = 'w-full rounded-lg border px-4 py-3 text-left text-sm font-medium border-rose-400 bg-rose-50 text-rose-800';
          } else {
            optionStyles = 'w-full rounded-lg border px-4 py-3 text-left text-sm font-medium border-slate-100 text-slate-400';
          }

          return (
            <button
              key={key}
              onClick={() => handleOptionClick(key)}
              className={optionStyles}
            >
              <span className="inline-block w-6 font-bold">{key}.</span> {optionText}
            </button>
          );
        })}
      </div>

      {isAnswered && (
        <div className="mt-6 rounded-lg border border-slate-200 bg-gray-200 p-4 text-sm text-gray-700 dark:text-black">
          <p className="mb-3">
            {selectedOption?.toUpperCase() === currentQuestion.correct_option?.toUpperCase() ? (
              <span className="font-bold text-emerald-700 dark:text-emerald-700">Correct.</span>
            ) : (
              <span className="font-bold text-rose-600 dark:text-rose-600">Incorrect.</span>
            )}
            {currentQuestion.explanation && ` ${currentQuestion.explanation}`}
          </p>
          <button
            onClick={handleNext}
            className="float-right w-full rounded-lg bg-slate-900 px-5 py-2 text-xs font-medium text-white shadow-sm transition hover:bg-teal-800 sm:w-auto"
          >
            {currentIdx + 1 === questions.length ? 'Finish Quiz' : 'Next Question'}
          </button>
          <div className="clear-both"></div>
        </div>
      )}
    </div>
  );
}
