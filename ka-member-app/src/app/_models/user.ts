import { Role } from "./role";

export class User {
    id: number;
    username: string;    
    role: Role;
    suspended: boolean;
    fullname: string;
    password?: string;
    accessToken?: string;
    isDeleting: boolean = false;

    constructor(obj?: any) {

        this.id = obj && obj.id || null;
        this.username = obj && obj.username || null;
        this.role = obj && obj.role || null;
        this.suspended = obj && obj.suspended;
        this.fullname = obj && obj.fullname || null;
        this.password = obj && obj.password || null;
        this.accessToken = obj && obj.accessToken || null;
    }
}