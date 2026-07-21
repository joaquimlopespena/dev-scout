import { Link } from '@inertiajs/react';

type BrandLogoProps = {
    compact?: boolean;
    href?: string;
};

export default function BrandLogo({ compact = false, href }: BrandLogoProps) {
    const logo = compact ? (
        <span className="brand-lockup brand-lockup-compact">
            <img src="/brand/devscout-icon.png" alt="" />
            <span>DevScout</span>
        </span>
    ) : (
        <img className="brand-logo" src="/brand/devscout-logo.png" alt="DevScout" />
    );

    return href ? (
        <Link href={href} className="brand-link" aria-label="DevScout">
            {logo}
        </Link>
    ) : (
        logo
    );
}
