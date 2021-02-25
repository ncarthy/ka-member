import { Role } from "./role";
export class User {
    id: number;
    username: string;
    fullname: string;
    role: Role;
    accessToken?: string;
}