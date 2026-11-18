import { Link } from '@inertiajs/react';
import { createContext, useContext, useEffect, useRef, useState } from 'react';

const DropdownContext = createContext(null);

function Dropdown({ children }) {
    const [open, setOpen] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        const onPointerDown = (event) => {
            if (ref.current && !ref.current.contains(event.target)) {
                setOpen(false);
            }
        };
        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', onPointerDown);
        document.addEventListener('keydown', onKeyDown);

        return () => {
            document.removeEventListener('mousedown', onPointerDown);
            document.removeEventListener('keydown', onKeyDown);
        };
    }, [open]);

    return (
        <DropdownContext.Provider value={{ open, setOpen }}>
            <div className="relative" ref={ref}>
                {children}
            </div>
        </DropdownContext.Provider>
    );
}

function Trigger({ children, className = '' }) {
    const { open, setOpen } = useContext(DropdownContext);

    return (
        <button
            type="button"
            aria-haspopup="menu"
            aria-expanded={open}
            onClick={() => setOpen((value) => !value)}
            className={`flex items-center gap-2 rounded-full border border-stone-200 bg-white py-1 pl-1 pr-3 text-left hover:bg-stone-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 ${className}`}
        >
            {children}
        </button>
    );
}

function Content({ align = 'right', children }) {
    const { open, setOpen } = useContext(DropdownContext);

    if (!open) {
        return null;
    }

    return (
        <div
            role="menu"
            onClick={() => setOpen(false)}
            className={`absolute z-50 mt-2 w-60 rounded-2xl border border-stone-200 bg-white p-1.5 ${
                align === 'left' ? 'left-0' : 'right-0'
            }`}
        >
            {children}
        </div>
    );
}

function DropdownLink({ className = '', children, ...props }) {
    return (
        <Link
            {...props}
            role="menuitem"
            className={`block w-full rounded-xl px-3 py-2 text-left text-sm font-medium text-stone-800 hover:bg-stone-100 ${className}`}
        >
            {children}
        </Link>
    );
}

Dropdown.Trigger = Trigger;
Dropdown.Content = Content;
Dropdown.Link = DropdownLink;

export default Dropdown;