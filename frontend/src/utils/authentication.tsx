import { useQuery } from "@tanstack/react-query";
import { makeSnakeCase } from "./string";
import Keycloak from 'keycloak-js';
import axios from "axios";
interface UserConstructParams {
  username: string;
  roles: Array<string>;
  authenticated: boolean;
}

export class User{
  username: string;
  roles: Array<string>;
  authenticated: boolean;

  constructor({ username, roles, authenticated }: UserConstructParams){
    this.username = username;
    this.roles = roles;
    this.authenticated = authenticated;
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

const keycloak = new Keycloak({
  url: __AUTHURL__,
  realm: 'cern',
  clientId: __CLIENTID__,
});

let backendToken = '';

async function requestBackendToken(subjectToken: string|undefined){
  const response = await axios({
    url: __TOKEN_EXCHANGE_URL__,
    method: 'post',
    params: {
      clientId: __CLIENTID__,
      subject_token: subjectToken? subjectToken: '',
      audience: __BACKEND_CLIENTID__,
      grant_type: 'urn:ietf:params:oauth:grant-type:token-exchange',
      requested_token_type: 'urn:ietf:params:oauth:token-type:access_token'
    }
  }).then((res) =>res.data);
  return response.access_token;
}

async function authenticate(){
  if (!keycloak.authenticated){
    if (keycloak.isTokenExpired(__KEYCLOAK_REFRESH_TIME__)){
      await keycloak.updateToken(__KEYCLOAK_REFRESH_TIME__)
    } else {
      await keycloak.init({
        onLoad: 'login-required'
      });
    }
    backendToken  = await requestBackendToken(keycloak.token);
  }
  return {
    keycloak,
    backendToken
  }
}

export function useKeycloak(): any {
  const {isError, isPending, data, error} = useQuery({
    queryKey: ['keycloak', 'authenticate', {}],
    queryFn: authenticate,
    staleTime: __KEYCLOAK_REFRESH_TIME__*1000 // Miliseconds
  });
  return {
    ...data,
    isPending,
    isError,
    error
  };
}

export function useUser(): User{
  const {
    keycloak,
    isPending: keycloakIsPending,
    isError: keycloakIsError,
    error: keycloakError } = useKeycloak();
  if (keycloakIsError){
    console.log('An error in KeyCloak authentication occured');
    console.log(keycloakError);
    return new User({
      username: 'Error',
      roles: [],
      authenticated: false
    });
  } else if (keycloakIsPending){
    return new User({
      username: 'Loading',
      roles: [],
      authenticated: false
    });
  }
  return new User({
    username: keycloak.tokenParsed.username,
    roles: keycloak.tokenParsed.cern_roles,
    authenticated: true
  });
}
