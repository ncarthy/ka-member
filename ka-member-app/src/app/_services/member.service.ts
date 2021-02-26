import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Member } from '@app/_models';

import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

@Injectable({ providedIn: 'root' })
export class MemberService {
    constructor(private http: HttpClient) { }

    getAll() {
        return this.http.get<Member[]>(`${environment.apiUrl}/member/read.php`).pipe(
            map(response => {  
                return <any>response['records'];
            })
        );
    }

    
    getById(id: number) {
        return this.http.get<Member>(`${environment.apiUrl}/member/read_one.php?id=${id}`);
    }
}