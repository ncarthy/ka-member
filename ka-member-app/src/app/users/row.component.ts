import {
    Component,
    EventEmitter,
    Input,
    Output
} from '@angular/core';
import { User, Role } from '../_models';
import { UserService, AlertService } from '@app/_services';
import { first } from 'rxjs/operators';
/**
* @UserRow: A component for the view of single User
*/
@Component({
    selector: 'tr[user-row]',
    templateUrl: './row.component.html',
})
export class RowComponent {
    roles = Object.keys(Role).map((key :string) => Role[key as Role]);
    roles2 = Role;

    @Input() user!: User;
    @Output() onUserDeleted: EventEmitter<User>;

    constructor(
        private userService: UserService,
        private alertService: AlertService) 
    {
        this.onUserDeleted = new EventEmitter();
    }

    deleteUser(id: number) {
        if (!this.user) return;
        this.user.isDeleting = true;
        this.userService.delete(id)
            .pipe(first())
            .subscribe(() => {
                this.alertService.success('User deleted', { keepAfterRouteChange: true });
                this.onUserDeleted.emit(this.user);
            });
    }

}