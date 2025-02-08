import { useQuery } from "@tanstack/react-query";
import { makeSnakeCase } from "./string";

interface UserConstructParams {
  username: string;
  roles: Array<string>;
}

export class User{
  username: string;
  roles: Array<string>;

  constructor({ username, roles }: UserConstructParams){
    this.username = username;
    this.roles = roles;
  }

  get highestRole(): string{
    if (this.isAdmin()){
      return 'admin';
    } else if (this.isDeleter()){
      return 'deleter';
    } else if (this.isEditor()){
      return 'editor';
    } else if (this.isViewer()){
      return 'viewer';
    } else {
      return 'none';
    }
  }

  isRole(role: string): Boolean{
    return this.roles.includes(makeSnakeCase(role));
  }

  isAdmin(): Boolean{
    return this.roles.includes('admin');
  }

  isDeleter(): Boolean{
    return this.roles.includes('deleter');
  }

  isEditor(): Boolean{
    return this.roles.includes('editor');
  }

  isViewer(): Boolean{
    return this.roles.includes('viewer');
  }

  canAll(): Boolean{
    return this.isAdmin();
  }

  canDelete(): Boolean{
    return this.isDeleter();
  }

  canRestore(): Boolean{
    return this.isDeleter();
  }

  canRegister(): Boolean{
    return this.isEditor();
  }

  canEdit(): Boolean{
    return this.isEditor();
  }

  canUpload(): Boolean{
    return this.isEditor();
  }

  canDownload(): Boolean{
    return this.isEditor();
  }

  canView(): Boolean{
    return this.isViewer();
  }
}

export function useUser(): User{
    // The backend ensures that the typing is met but I don't know
    // how to make typescript believe in me
  let queryReturn: any = useQuery({
    queryKey: ['user', 'get', {}]
  })

  // User params that makes every role be negated
  const loadingUser = {
    username: 'Loading',
    roles: []
  }

  if (queryReturn.isError){
    console.log('Error retrieving user data');
    console.log(queryReturn.data);
    return new User(loadingUser);
  } else if(queryReturn.isPending){
    return new User(loadingUser);
  }
  return new User(queryReturn.data.data);
}
