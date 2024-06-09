import { useState, useEffect } from 'react';
import { CheckCircleIcon, ExclamationCircleIcon, ExclamationTriangleIcon, InformationCircleIcon } from '@heroicons/react/24/outline';

export default function Alert({ message, type, timeOut = 7000 }) {
    const alertIcon = {
        success: <CheckCircleIcon className="h-4 w-4" />,
        error: <ExclamationCircleIcon className="h-4 w-4" />,
        warning: <ExclamationTriangleIcon className="h-4 w-4" />,
        info: <InformationCircleIcon className="h-4 w-4" />,
    };

    const [visible, setVisible] = useState(true);

    const handleClose = () => {
        setVisible(false);
    };

    useEffect(() => {
        const timer = setTimeout(() => {
            setVisible(false);
        }, timeOut);

        return () => clearTimeout(timer);
    }, []);

    return (
        <>
            {visible && (
                <div
                    role="alert"
                    className={`alert alert-${type} `}
                    style={{ transition: 'opacity 0.9s ease' }}
                >
                    {alertIcon[type]}
                    {/* Check the type of message and render accordingly */}
                    {typeof message === 'string' ? (
                        <div>{message}</div>
                    ) : (
                        <ul>
                            {Object.keys(message).map((key, index) => (
                                <li key={index}>{message[key]}</li>
                            ))}
                        </ul>
                    )}
                    {/* close icon to close the alert */}
                    <button
                        className="btn btn-ghost btn-square"
                        aria-label="Close"
                        onClick={handleClose}
                    >
                        <svg
                            className="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            )}
        </>
    );
}