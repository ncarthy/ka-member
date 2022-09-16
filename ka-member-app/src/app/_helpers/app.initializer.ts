import { AuthenticationService } from '@app/_services';
import { TeardownLogic } from 'rxjs/internal/types';

export function appInitializer(authenticationService: AuthenticationService) {
    return () => new Promise((resolve:any) => {
        // attempt to refresh token on app start up to auto authenticate
        authenticationService.refreshToken()
            .subscribe()
            .add(resolve as TeardownLogic);
    });
}