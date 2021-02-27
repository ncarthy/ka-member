import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { User } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class UserService {
    constructor(private http: HttpClient) { }

    getAll() {
        return this.http.get<any>(`${environment.apiUrl}/user/read.php`).pipe(
            map(response => {return <User[]>response['records']})
        );
    }

    
    getById(id: number) {
        return this.http.get<User>(`${environment.apiUrl}/user/read_one.php?id=${id}`);
    }
}