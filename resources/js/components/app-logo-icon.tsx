import { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <img 
            {...props} 
            src="/assets/logo.svg" 
            alt="Spotless HR Logo" 
            className={props.className}
        />
    );
}
