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

    onSuspendedChange(value : any) {
        if (!this.user) return;
        this.user.isUpdating = true;
        this.user.suspended = !this.user.suspended;
        this.userService.update(this.user.id, this.user)
        .pipe(first())
        .subscribe(() => {
            this.alertService.success('User Updated', { keepAfterRouteChange: true });
            this.user.isUpdating = false;
        });
    }

    onRoleChange(value : Role) {
        if (!this.user) return;
        this.user.isUpdating = true;      
        this.user.role = value;  
        this.userService.update(this.user.id, this.user)
        .pipe(first())
        .subscribe(() => {
            this.alertService.success('User Updated', { keepAfterRouteChange: true });
            this.user.isUpdating = false;
        });
    }

}