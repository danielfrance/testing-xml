import { Link } from '@inertiajs/react';

export default function NavLink({ active = false, className = '', children, ...props }) {
    return (
        <Link
            {...props}
            className={
                'inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 focus:outline-none  transform hover:translate-x-4 transition-transform ease-in duration-200' +
                (active
                    ? 'border-indigo-400 focus:border-indigo-700 text-primary'
                    : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300 focus:text-gray-700 focus:border-gray-300 ') +
                className
            }
        >
            {children}
        </Link>
    );
}
